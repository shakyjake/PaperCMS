<?php

	/**
	 * Send all emails in the email queue
	 * @param int $email_count [required] The maximum number of emails to process
	 * @return undefined
	 */
	function email_queue_process(int $email_count = 0){

		$sent_emails = [];

		$RS = get_records('emailQueue_List', $email_count);
		while(!$RS->eof){

			$id = $RS->row['MessageId'];
			$headers = $RS->row['Headers'];
			$html = has_value($headers['MIME-Version']);

			$email = new Email($RS->row['Subject'], $RS->row['Body'], $RS->row['Recipient'], $RS->row['Sender'], $html, $RS->row['Attachments']);
			foreach($headers as $key => $value){
				$email->header_set($key, $value);
			}
			if($email->send()){
				$sent_emails[] = $id;
			}

			$RS->move_next();
		}

		if(count($sent_emails)){
			foreach($sent_emails as $id){
				execute_sql('emailQueue_remove', $id);
			}
		}

	}

?>