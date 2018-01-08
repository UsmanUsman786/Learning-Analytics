<?php
header('Access-Control-Allow-Origin: *');   
date_default_timezone_set('Asia/Karachi');
session_start();
include("functions.php");

$req = $_REQUEST['REQUEST_TYPE'];

switch($req)
{

    // controller ftn to call login
	case 'LOGIN':      
        echo fn_Login();
        break;

    // get registered courses of a student 
    case 'get_registeredCourses': 

    	echo fn_get_registeredCourses();
    break;

	//view student weak topics
	case 'VIEW_WEAK_TOPICS': 
        echo fn_view_weak_topics();
        break;

    case 'GET_WEAK_TOPIC_MATERIAL': // for student to get material corresponding to a weak topic 
        echo fn_get_weak_topic_material();
        break;

    case 'GET_COURSE_MATERIAL':   // for instructor to get material of the course
        echo fn_get_course_material();
        break;
   
	//HOD report of drop out students
    case 'Predicted_DropoutStudents':
        echo fn_Predicted_DropoutStudents();
        break;

    // for individual student to get predicted marks corresponding to courses
    case 'AllCoursesPredictedmarks': //done
        echo fn_AllCoursesPredictedmarks();
        break;

    // to get a teacher registered courses for current semester
    case 'teacherCourses': //done
        echo fn_teacherCourses();
        break;

	//all sections that a teacher is teaching
    case 'getSections': //done
        echo fn_getSections();
        break;

	//retrieve assessments to take feedback on them from the student
    case 'assessment4Feedback': //done
        echo fn_assessment4Feedback();
        break;

	//get assessment-wise topics
    case 'get_assessmentTopics': //done
        echo fn_get_assessmentTopics();
        break;

	//save the feedback of the student
    case 'give_Feedback':
        echo fn_give_Feedback();
        break;



        // for saving feedback
    case 'save_feedback':
        echo fn_save_feedback();
        break;
        
    // showing assessments feedback.
    case 'assessments_in_feedback':
        echo fn_assessments_in_feedback();
        break;






        //most problematic topics of the course
    case 'ProblematicTopics': 
        echo fn_ProblematicTopics();
        break;


        // weak topic report
    case 'weakTopic_report': //done
        echo fn_weakTopicReport();
        break;

    case 'topicwise_studentsDetail':
        echo fn_topicwise_studentsDetail();
        break;


    case 'Predicted_marks_report': //done
        echo fn_Predicted_marks_report();
        break;
    case 'Markswise_studentsDetail':
        echo fn_Markswise_studentsDetail();
        break;
}

?>