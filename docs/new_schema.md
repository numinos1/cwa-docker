# User
- id int
- call_sign varchar

- first_name varchar
- last_name varchar

- email varchar
- phone varchar

- city varchar
- state varchar
- zip_code varchar
- country varchar
- time_zone tinyint
- languages varchar

- wpm varchar
- level varchar

- youth char
- age char
- parent varchar
- parent_email varchar

- verify_email_date varchar
- verify_email_number tinyint
- verify_response char

- date_created datetime
- date_updated timestamp

# cwa_student 747
- start_time varchar
- request_date varchar
- semester varchar
- notes text
- welcome_date varchar
- email_sent_date varchar
- email_number tinyint
- response char
- response_date varchar
- response_number tinyint
- student_status char
- action_log text
- pre_assigned_advisor varchar
- selected_date varchar
- passed_over_count tinyint
- hold_override char
- messaging char
- assigned_advisor varchar
- advisor_select_date varchar
- advisor_class_timezone tinyint
- hold_reason_code char
- class_priority tinyint
- assigned_advisor_class char
- promotable char
- excluded_advisor varchar
- student_survey_completion_date varchar
- available_class_days varchar
- intervention_required char
- copy_control varchar
 
- first_class_choice varchar
- second_class_choice varchar
- third_class_choice varchar
- first_class_choice_utc varchar
- second_class_choice_utc varchar
- third_class_choice_utc varchar

- date_created datetime
- date_updated timestamp

# cwa_advisornew 64
- select_sequence smallint
- text_message char
- semester varchar
- survey_score tinyint
- fifo_date varchar
- welcome_email_date varchar

- class_verified char
- control_code varchar

- action_log text

- date_created datetime
- date_updated timestamp
