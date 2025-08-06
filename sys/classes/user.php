<?php 

class User {
	
	public ?int $id = null;
	public ?array $data = null;
	public ?string $login_token = null;
	private ?int $group = 1;
	public ?string $email_address = null;
	public ?string $phone_number = null;
	public ?string $user_name = null;
	public ?string $password = null;
	public ?string $profile_picture = null;
	public ?string $csrf_salt = null;
	private bool $guest_mode = false;
	private bool $data_loaded = false;

	function imitate_guest(?bool $guest_mode = true){

		if(is_null($guest_mode)){
			$guest_mode = true;
		}

		$this->guest_mode = $guest_mode;

	}

	function group(){

		if($this->guest_mode){
			return 1;
		}

		return $this->group;

	}

	function data(string $key){

		if($this->guest_mode){
			return '';
		}

		if(array_key_exists($key, $this->data)){
			return $this->data[$key]->value;
		}

		if(!$this->data_loaded){
			$this->load_data();
		}

		if(array_key_exists($key, $this->data)){
			return $this->data[$key]->value;
		}

		return '';

	}

	function load_data(){

		if($this->guest_mode){
			return;
		}

		$this->data_loaded = true;

		$params = [
			$this->id
		];

		$RS = get_records('userData_List', $params);

		while(!$RS->eof){
			$this->data[$RS->row['DataKey']] = $RS->row['DataValue'];
			$this->data[$RS->row['DataKey']] = new UserData($this->id, $RS->row['UserDataId'], $RS->row['DataKey'], $RS->row['DataValue'], $RS->row['Encrypted']);
			$RS->move_next();
		}

		$RS = null;

	}

	function can_edit(Page $page){
		
		if($this->guest_mode){
			return false;
		}

		// todo -> this

		return false;

	}

	function logged_in(){

		if($this->guest_mode){
			return false;
		}

		if(empty($this->login_token) && (empty($this->user_name) || empty($this->password))){
			return false;
		}

		return $this->log_in();

	}

	function log_in(bool $persist_login = false){

		/* some fields already covered by class properties/functions */
		$hidden_fields = [
			'UserId',
			'Password',
			'UserGroupId',
			'UserName',
			'LoginToken'
		];

		if(empty($this->login_token)){
		
			if(empty($this->user_name) || empty($this->password)){
				return false;
			}
			
			$RS = get_records('user_Detail_Login', [
				$this->user_name
			]);

			while(!$RS->eof){
			
				if(password_verify($this->password, $RS->row['Password'])){
	
					$this->profile_picture = sprintf('https://www.gravatar.com/avatar/%1$s?s=80', hash('sha256', $RS->row['EmailAddress']));
					$this->group = (int)$RS->row['UserGroupId'];
					$this->id = (int)$RS->row['UserId'];
	
					foreach($RS->row as $key => $value){
						if(in_array($key, $hidden_fields)){
							continue;
						}
						$this->data[$key] = $value;
						$this->data[$key] = new UserData($this->id, null, $key, $value);
					}
	
					if($persist_login){
						$this->login_token = get_single_value('userLogin_Save', [
							$this->id,
							$this->user_name,
							$RS->row['Password']
						]);
					}

					session_set('UserName', $this->user_name);
					session_set('Password', $this->password);
	
					return true;
	
				}

				$RS->move_next();

			}

			$RS = null;

			return false;
			
		} else {
			
			$RS = get_records('userLogin_Validate', [
				$this->login_token
			]);
			
			if($RS->eof){
				$RS = null;
				return false;
			}

			$this->user_name = $RS->row['UserName'];
			$this->profile_picture = sprintf('https://www.gravatar.com/avatar/%1$s?s=80', hash('sha256', $RS->row['EmailAddress']));
			$this->group = $RS->row['UserGroupId'];
			$this->id = $RS->row['UserId'];

			foreach($RS->row as $key => $value){
				if(in_array($key, $hidden_fields)){
					continue;
				}
				$this->data[$key] = $value;
			}
			
			cookie_set('LoginToken', $this->login_token, [
				'expires' => time() + 2419200
			]);

			return true;

		}
		
		return false;

	}

	function log_out(){
		
		$this->data = null;
		$this->group = null;
		$this->user_name = null;
		$this->password = null;
		$this->guest_mode = false;
		$this->login_token = null;
		$this->profile_picture = null;
		
		cookie_remove('LoginToken');
		session_remove('UserName');
		session_remove('Password');
		session_remove('GuestMode');

	}

	function __construct(){
		
		$this->data = [];
		$this->group = 1;
		$this->user_name = null;
		$this->password = null;
		$this->guest_mode = false;
		
		$this->login_token = cookie_get('LoginToken');
		$this->user_name = session_get('UserName');
		$this->password = session_get('Password');
		$this->guest_mode = session_get('GuestMode') === '1';
		$this->csrf_salt = session_get('CSRFSalt');

	}

	function __destruct(){

		if(session_status() === PHP_SESSION_ACTIVE){
		
			if(!empty($this->login_token)){
				cookie_set('LoginToken', $this->login_token);
			}
			if(!empty($this->user_name)){
				session_set('UserName', $this->user_name);
			}
			if(!empty($this->password)){
				session_set('Password', $this->password);
			}
			if(!empty($this->csrf_salt)){
				session_set('CSRFSalt', $this->csrf_salt);
			}
			if($this->guest_mode){
				session_set('GuestMode', '1');
			} else {
				session_remove('GuestMode');
			}

		}
		
		$this->data = null;
		$this->group = null;
		$this->user_name = null;
		$this->password = null;
		$this->profile_picture = null;

	}

}

class UserData {
	
	public $id = null;
	public $user_id = null;
	public $name = null;
	public $value = null;
	public $encrypted = null;
	
	function save(){
		execute_sql('userData_Save', [
			[$this->id, SQLSRV_PARAM_OUT],
			$this->user_id,
			$this->name,
			$this->value,
			$this->encrypted
		]);
	}
	
	function load(){
		$RS = get_records('userData_Detail', [
			$this->id,
			$this->user_id,
			$this->name
		]);
		if(!$RS->eof){
			$this->user_id = $RS->row['UserId'];
			$this->name = $RS->row['DataKey'];
			$this->value = $RS->row['DataValue'];
		}
	}

	function delete(){
		execute_sql('userData_Delete', [
			$this->id
		]);
	}
	
	function __construct(int $user_id, ?int $user_data_id = null, ?string $name = null, ?string $value = null, ?bool $encrypted = false){
		if(empty($user_id)){
			throw new Exception('User Data must have an associated User.');
		}
		$this->id = $user_data_id;
		$this->user_id = $user_id;
		$this->name = $name;
		$this->value = $value;
		$this->encrypted = $encrypted;
	}
	
	function __destruct(){
		$this->id = null;
		$this->user_id = null;
		$this->name = null;
		$this->value = null;
		$this->encrypted = null;
	}

}

function user_login(){

	global $current_user;

	header('Content-Type: application/json');

	$current_user->log_out();

	$response = new AJAXResponse();

	if(empty($_POST['email'])){
		$response->status_code = 400;
		$response->message_add('Please enter your email address.', 'email', 'error');
	}

	if(empty($_POST['password'])){
		$response->status_code = 400;
		$response->message_add('Please enter your password.', 'password', 'error');
	}

	if($response->status_code !== 200){
		$response->output();
		die();
	}

	$persist_login = false;
	if(!empty($_POST['persist_login'])){
		$persist_login = ($_POST['persist_login'] === '1');
	}

	$current_user->login_token = null;
	$current_user->user_name = $_POST['email'];
	$current_user->password = $_POST['password'];

	if($current_user->log_in($persist_login)){
		$response->status_code = 200;
		$response->message_add('Login successful.', '', 'success');
		if($current_user->group() > 2){
			$response->redirect = '/admin';
		} else {
			$response->redirect = '/';
		}
	} else {
		$response->status_code = 400;
		$response->messages['form'] = 'Invalid username or password.';
	}

	$response->output();

	die();

}
ajax_add('user', 'login');

function user_save(){

	global $current_user;

	$response = new AJAXResponse();

	$id = $current_user->id;
	$max_group_id = $current_user->group();
	$group_id = do_form('group_id', $current_user->group());
	if($group_id > $max_group_id){/* don't be getting ideas above your station */
		$group_id = $max_group_id;
	}
	$user_name = do_form('user_name', $current_user->user_name);
	$password = do_form('password', $current_user->password);
	$email_address = do_form('email_address', '');
	$phone_number = do_form('phone_number', '');

	if(empty($user_name)){
		$response->status_code = 400;
		$response->message_add('Please enter a username.', 'user_name', 'error');
	}

	if(empty($password)){
		$response->status_code = 400;
		$response->message_add('Please enter a password.', 'password', 'error');
	}

	if($response->status_code !== 200){
		$response->output();
		die();
	}

	$RS = get_records('user_Save', [
		$id,
		$group_id,
		$email_address,
		$phone_number,
		$password,
		$user_name,
		$current_user->id
	]);

	$response->rs_add($RS, 400);

	$RS = null;

	$response->output();

	die();

}
ajax_add('user', 'save');

function user_search_admin(){

	global $current_user;

	$response = new AJAXResponse();

	$search = do_form('search', '');
	$group_id = do_form('group_id', '');
	$page = do_form('pagination_page', 1);
	$per_page = do_form('pagination_per_page', 25);
	
	if($response->status_code !== 200){
		$response->output();
		die();
	}

	$RS = get_records('user_Search', [
		$search,
		$group_id,
		$current_user->id,
		$page,
		$per_page
	]);

	$response->pagination = new Pagination('/ajax/user/search-admin', $RS, $page, $per_page);

	$response->debug = [
		$search,
		$group_id,
		$current_user->id,
		$page,
		$per_page
	];

	if(!$RS->eof){
	
		$response->pagination->total_records = $RS->row['ResultCount'];
		$response->pagination->total_pages = ceil($response->pagination->total_records / $response->pagination->per_page);

		foreach($RS->rows as $row){

			$response_row = [
				'id' => $row['UserId'],
				'parent_id' => null,
				'type' => $row['UserGroupName'],
				'name' => $row['Name'],
				'img' => '',
				'date' => human_date($row['DateCreated']),
				'author' => $row['EmailAddress'],
				"detail_url" => '/ajax/user/detail-admin/' . $row['UserId'],
			];

			$response->data[] = $response_row;

		}

	}

	$RS = null;

	$response->output();

	die();

}
ajax_add('user', 'search_admin');

function user_group_list_admin(){

	global $current_user;

	$response = new AJAXResponse();

	$search = do_form('search', '');
	$group_id = do_form('type', 'page');
	$page = do_form('pagination_page', 1);
	$per_page = do_form('pagination_per_page', 25);
	
	if($response->status_code !== 200){
		$response->output();
		die();
	}

	$RS = get_records('userGroup_List', [
		$current_user->id
	]);
	
	if($RS->eof){

		$response->status_code = 404;

	} else {

		foreach($RS->rows as $row){
			$response_row = [
				'id' => $row['UserGroupId'],
				'name' => $row['UserGroupName']
			];
			$response->data[] = $response_row;
		}
	
	}

	$RS = null;

	$response->output();

	die();

}
ajax_add('user_group', 'list_admin');
?>