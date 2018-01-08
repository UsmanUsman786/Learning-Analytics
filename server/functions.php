<?php
header('Access-Control-Allow-Origin: *');
date_default_timezone_set('Asia/Karachi');
include("servermodal.php");


function fn_Login(){

	$usename = $_REQUEST['USERNAME'];
	$pass = $_REQUEST['PASSWORD'];
	return LearningAnalytics::model_login($usename,$pass);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}


function fn_get_registeredCourses(){

	$uid = $_REQUEST['User_id'];
	
	return LearningAnalytics::model_get_registeredCourses($uid);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}

function fn_view_weak_topics(){

	$uid = $_REQUEST['User_id'];
	$cname = $_REQUEST['Course_name'];
	return LearningAnalytics::model_view_weak_topics($uid,$cname);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}

function fn_get_weak_topic_material(){

	$tid = $_REQUEST['Topic_id'];
	
	return LearningAnalytics::model_get_weak_topic_material($tid);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}

function fn_get_course_material(){ // for teacher
	$cid = $_REQUEST['Course_id'];
	return LearningAnalytics::model_get_course_material($cid);
}


function fn_Predicted_DropoutStudents(){

	$batch = $_REQUEST['batch'];
	
	return LearningAnalytics::model_Predicted_DropoutStudents($batch);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}

function fn_AllCoursesPredictedmarks(){

	$student_id = $_REQUEST['student_id'];
	
	return LearningAnalytics::model_AllCoursesPredictedmarks($student_id);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}


// new function added after providing code to moiz
function fn_teacherCourses(){

	$teacher_id = $_REQUEST['teacher_id'];
	$semester = $_REQUEST['semester'];

	return LearningAnalytics::model_teacherCourses($teacher_id, $semester);
}

function fn_getSections(){
	$cid = $_REQUEST['course_id'];
	$semester = $_REQUEST['semester'];
	return LearningAnalytics::model_getSections($cid,$semester);
}

function fn_assessment4Feedback(){

	$student_id = $_REQUEST['student_id'];
	$course_name = $_REQUEST['course_name'];
	
	return LearningAnalytics::model_assessment4Feedback($student_id,$course_name);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}

function fn_get_assessmentTopics(){

	$ass_id = $_REQUEST['assessment_id'];
	
	return LearningAnalytics::model_get_assessmentTopics($ass_id);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}


function fn_give_Feedback(){

	$student_id = $_REQUEST['student_id'];
	$topic_id = $_REQUEST['topic_id'];
	$reason_id = $_REQUEST['reason_id'];
	
	return LearningAnalytics::model_give_Feedback($student_id,$topic_id,$reason_id);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}


function fn_save_feedback()
{
	$student_id = $_REQUEST['student_id'];
	$topic_ids = $_REQUEST['topic_ids'];
	$topic_names = $_REQUEST['topic_names'];
	$options1 = $_REQUEST['options1'];
	$options2 = $_REQUEST['options2'];
	$num_of_records = $_REQUEST['num'];
	$ass_id = $_REQUEST['ass_id'];
	//$courseID = $_REQUEST['courseID'];
	
	//saving courseId as 1 always for now
	return LearningAnalytics::saveFeedback($student_id, $topic_ids, $topic_names, $options1, $options2, $num_of_records, $ass_id, 1);
}

function fn_assessments_in_feedback()
{
	$student_id = $_REQUEST['student_id'];
	$courseID = $_REQUEST['courseID'];
	return LearningAnalytics::assessmentsInFeedback($student_id, $courseID);
}


function fn_ProblematicTopics(){
	$course_id = $_REQUEST['course_id'];
	return LearningAnalytics::model_ProblematicTopics($course_id);
}




function fn_weakTopicReport(){
	$course_id = $_REQUEST['course_id'];
	$section = $_REQUEST['section'];
	return LearningAnalytics::weakTopicReport($course_id, $section);
}


function fn_topicwise_studentsDetail(){
	$course_id = $_REQUEST['course_id'];
	$section = $_REQUEST['section'];
	$topic_name = $_REQUEST['topic_name'];
	
	return LearningAnalytics::topicwise_studentsDetail($course_id, $section, $topic_name);	
}



function fn_Predicted_marks_report(){

	$tid = $_REQUEST['teacher_id'];
	$cid = $_REQUEST['course_id'];
	$section = $_REQUEST['section'];
	return LearningAnalytics::model_Predicted_marks_report($cid,$tid,$section);
	//echo Portal::server_login("chzunair453@gmail.com","12345","admin");
}

function fn_Markswise_studentsDetail(){
	$course_id = $_REQUEST['course_id'];
	$section = $_REQUEST['section'];
	$rgrade = $_REQUEST['r_grade'];
	
	return LearningAnalytics::Markswise_studentsDetail($course_id, $section, $rgrade);	
}

?>