<?php

class Email {
	
	protected $subject = null;
	protected $sender = null;
	protected $recipient = null;
	protected $body = null;
	protected $html = null;
	protected $attachments = null;
	protected $additional_headers = null;
	
	function send(){
	
		$separator = md5($_SERVER['HTTP_HOST'] . time());

		$headers = [];

		if(has_value($this->attachments)){
			$this->html = true;
		}

		if($this->html){
			$headers['MIME-Version'] = '1.0';
			if(!has_value($this->attachments)){
				$headers['Content-type'] = 'text/html; charset=utf-8';
			} else {
				$headers['Content-type'] = 'multipart/mixed; boundary="' . $separator . '"';
				$headers['Content-Transfer-Encoding'] = '7bit';
			}
		}

		$body = [];

		if(has_value($this->attachments)){
			$body[] = '--' . $separator;
			$body[] = 'Content-type: text/html; charset=utf-8';
			$body[] = 'Content-Transfer-Encoding: 8bit';
		}

		$body[] = $this->body;

		if(has_value($this->attachments)){
			$attachment_list = explode(';', $this->attachments);
			foreach($attachment_list as $attachment){
				if(file_exists($attachment)){
					$body[] = '--' . $separator;
					$body[] = 'Content-Type: application/octet-stream; name="' . pathinfo($attachment, PATHINFO_BASENAME) . '"';
					$body[] = 'Content-Transfer-Encoding: base64';
					$body[] = 'Content-Disposition: attachment';
					$body[] = chunk_split(base64_encode(file_get_contents($attachment)));
					$body[] = '--' . $separator . '--';
				}
			}
		}
		
		if(count($this->additional_headers)){
			foreach($this->additional_headers as $key => $value){
				$headers[$key] = $value;
			}
		}

		$headers['To'] = $this->recipient;
		$headers['From'] = $this->sender;

		$sent = mail($this->recipient, $this->subject, implode('\r\n', $body), $this->headers_compat($headers));
		
		return $sent;
		
	}
	
	function header_set(string $key, string $value){
		$this->additional_headers[$key] = $value;
	}
	
	function headers_compat(array $all_headers){
		$output = [];
		foreach($all_headers as $key => $value){
			$output[] = $key . ': ' . $value;
		}
		return implode("\r\n", $output);
	}
	
	function queue(){
	
		$separator = md5($_SERVER['HTTP_HOST'] . time());

		$headers = [];

		if(has_value($this->attachments)){
			$this->html = true;
		}

		if($this->html){
			$headers['MIME-Version'] = '1.0';
			if(!has_value($this->attachments)){
				$headers['Content-type'] = 'text/html; charset=utf-8';
			} else {
				$headers['Content-type'] = 'multipart/mixed; boundary="' . $separator . '"';
				$headers['Content-Transfer-Encoding'] = '7bit';
			}
		}

		$body = [];
		
		if(count($this->additional_headers)){
			foreach($this->additional_headers as $key => $value){
				$headers[$key] = $value;
			}
		}

		$headers['To'] = $this->recipient;
		$headers['From'] = $this->sender;

		$signature_parts = json_encode($headers) . $this->subject . $this->body . $this->attachments;

		$signature = sprintf('%1$s/%2$s/%3$s', 
			hash('md5', $signature_parts),
			hash('sha256', $signature_parts),
			hash('sha512', $signature_parts)
		);

		$signature_parts = null;

		execute_sql('emailQueue_Save', [
			$this->subject,
			$this->recipient,
			$this->sender,
			$this->additional_headers,
			$this->body,
			$this->attachments,
			$signature
		]);
		
	}
	
	function __construct(string $subject = 'No subject given', string $message = 'No message given', string $recipient = 'jake@eskdale.net', string $sender = 'webmaster@hosting.eskdale.net', bool $html = true, string $attachments = null){
		$this->subject = $subject;
		$this->body = $message;
		$this->sender = $sender;
		$this->recipient = $recipient;
		$this->html = $html;
		$this->additional_headers = [];
		$this->attachments = $attachments;
	}
	
	function __destruct(){
		$this->subject = null;
		$this->body = null;
		$this->sender = null;
		$this->recipient = null;
		$this->html = null;
		$this->additional_headers = null;
		$this->attachments = null;
	}

}

function email_queue_process(){

	$RS = get_records('emailQueue_list');

	if(!$RS->eof){
		while(!$RS->eof){
			$email = new Email($RS->row['Subject'], $RS->row['Body'], $RS->row['Recipient'], $RS->row['Sender'], true, $RS->row['Attachments']);
			if($email->send()){
				execute_sql('emailQueue_Remove', [
					$RS->row['MessageId']
				]);
			}
			$email = null;
			$RS->move_next();
		}
	}

}

function chunk_split(?string $data = '', ?int $line_length = 65){
	$lines = [];
	$line_count = ceil(strlen($data) / $line_length);
	for($i = 0; $i < $line_count; $i++){
		$lines[] = substr($data, $i * $line_length, $line_length);
	}
	return implode("\r\n", $lines);
}

?>