<?php
header('Access-Control-Allow-Origin: *');
date_default_timezone_set('Asia/Karachi');
class LearningAnalytics {

/*
The following method shall be called at the start of each of the 
upcoming methods for establishing a connection with the database.
*/
    private static function establishConnection() {
        $conn = null;
        $conn =  new mysqli('localhost', 'root', '', 'learninganalytics');
        return $conn;
    }

/*
Following method shall be used to verify the login credentials of 
the teacher or student. It returns success in case of a successful login,
else returns failure.
*/
    // need to change query
    public static function model_login($username,$password){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare(("SELECT ID, type FROM user WHERE username = ? AND password = ? "));
        $sql->bind_param("ss",$username,$password);

        $sql->execute();    // execute the query
        $sql->bind_result($id, $type);
        if($sql->fetch())
        {
            $json["STATUS"] = "SUCCESS";                   
            $json["MESSEGE"] = "Login Successful";
            $json["user_type"] = $type;
            $json["uid"] = $id;

            //@session_start();
            //$_SESSION['session']="ValueAssigned";
        }
        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Login Failed";
            $json["user_type"] = "";
            $json["uid"] = "";
        }
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }


 /*
The following function is used to obtain the course of a particular student from
a list of offered courses.
*/
    public static function model_get_registeredCourses($uid){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare(("SELECT course.ID, course.name FROM course, offeredCourse, registeredCourse WHERE registeredCourse.studentID =? AND registeredCourse.ID=offeredCourse.ID AND offeredCourse.ID=course.ID"));  
        $sql->bind_param("i",$uid);

        if ($sql->execute())
        {
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "get student registered courses Successfully";
        }
        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get student registered courses";
        }
        $counter = 0;
        $sql->bind_result($cid, $cname);
        while ($sql->fetch())
        {
            $temp["course_id"] = $cid;
            $temp["course_name"] = $cname;

            $json["DATA"][] = $temp;
            unset($temp);

            $counter++;
        }
        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }



/*
The following function is used to obtain the weak topics of a student
assessment-wise from a particular course.
First, it gets the list of courses in which the student is registered 
from the offered courses.
Then, it takes out the assessments of the student and finally, the topics 
of assessments in which he obtained less than 50% marks are extracted.
*/
    public static function model_view_weak_topics($uid, $cname){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare(("SELECT topic.ID, topic.name, assessment.type FROM studentAssessment, assessment, assessmentTopic, topic, offeredCourse WHERE studentAssessment.studentID =? AND offeredCourse.name =  ? AND studentAssessment.assessmentID = assessment.ID AND offeredCourse.ID = assessment.offeredcourseID AND assessmentTopic.assessmentID = assessment.ID AND assessmentTopic.topicID = topic.ID AND ( studentAssessment.marksObtained / assessment.totalMarks ) < 0.5"));  
        $sql->bind_param("is",$uid, $cname);


        $counter = 0;
        if ($sql->execute())
        {

            $sql->bind_result($topic_id,$topic, $assessment_type);
            while ($sql->fetch())
            {

                $temp["topic_id"] = $topic_id;
                $temp["topic_name"] = $topic;
                $temp["assessment_type"] = $assessment_type;

                $json["DATA"][] = $temp;
                unset($temp);

               $counter++;
            }



            $sql2 = $conn->prepare("SELECT topic.ID, topic.name FROM registeredCourse, offeredCourse, attendance, courseOutline, topic WHERE registeredCourse.studentID =? AND offeredCourse.name = ? AND registeredCourse.offeredCourseID = offeredCourse.ID AND registeredCourse.ID = attendance.registeredCourseID AND isPresent=0 AND offeredCourse.courseID = courseOutline.courseID AND courseOutline.topicID = topic.ID");  
            $sql2->bind_param("is",$uid, $cname);
            if($sql2->execute()){
            
                    $json["STATUS"] = "SUCCESS";
                    $json["MESSEGE"] = "get student weak topics including attendance Successfully";

                 $att_reason = "attendance";
                 $sql2->bind_result($topic_id,$topic_name);
                 while($sql2->fetch()){
                    $temp2["topic_id"] = $topic_id;
                    $temp2["topic_name"] = $topic_name;
                    $temp2["assessment_type"] = $att_reason;

                    $json["DATA"][] = $temp2;    
                    unset($temp);

                    $counter++;
         
                }
            }
            else
            {
                $json["STATUS"] = "FAIL";
            }

        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get student weak topics";
        }
        

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }


/*
The following function retrieves a list of students who are expected
to drop out in the current semester. This is done by obtaining those 
students whose SGPA (average of all courses GPA) is predicted to be 
less than 2.0 for that semester.
*/
    public static function model_Predicted_DropoutStudents($batch){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("SELECT temp.studentID,name,NIC,batch
                                FROM 
                                (SELECT student.studentID,AVG(predictedGPA) AS SGPA 
                                FROM registeredcourse, student
                                WHERE registeredcourse.studentID=student.studentID
                                GROUP BY studentID
                                HAVING SGPA <2)temp inner join student
                                WHERE student.studentID=temp.studentID and student.batch = ?");  
        $sql->bind_param("i",$batch);


        $counter = 0;
        if ($sql->execute())
        {

            $sql->bind_result($student_id,$name,$NIC, $batch);
            while ($sql->fetch())
            {

                $temp["student_id"] = $student_id;
                $temp["student_name"] = $name;
                $temp["NIC"] = $NIC;
                $temp["batch"] = $batch;

                $json["DATA"][] = $temp;
                unset($temp);

               $counter++;
            }
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "get dropout students Successfully";

        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get dropout students";
        }
        

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }

/*
The following function is used to obtain the predicted grade of a 
particular student in a particular course in which he is registered.
*/
    public static function model_AllCoursesPredictedmarks($student_id){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("SELECT registeredcourse.ID, name, predictedMarks, predictedGrade 
								FROM registeredCourse, offeredCourse 
								WHERE studentID =? AND registeredCourse.offeredCourseID = offeredCourse.ID");  
        $sql->bind_param("i",$student_id);


        $counter = 0;
        if ($sql->execute())
        {

            $sql->bind_result($course_id,$course_name,$predictedMarks, $predictedGrade);
            while ($sql->fetch())
            {

                $temp["reg_course_id"] = $course_id;
                $temp["course_name"] = $course_name;
                $temp["predictedMarks"] = $predictedMarks;
                $temp["predictedGrade"] = $predictedGrade;
                
                

                $json["DATA"][] = $temp;
                unset($temp);

               $counter++;
            }
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "get dropout students Successfully";

        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get dropout students";
        }
        

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }

/*
The following function is used to obtain the topic-wise material to
provide to the student.
*/
    public static function model_get_weak_topic_material($tid){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("SELECT topic.name, materialLink, type 
								FROM `coursematerial` JOIN topic on coursematerial.topicID = topic.ID 
								WHERE topicID = ?");  
        $sql->bind_param("i",$tid);


        $counter = 0;
        if ($sql->execute())
        {
            $json["STATUS"] = "SUCCESS";
            $sql->bind_result($topic_name,$material_link, $material_type);
            while ($sql->fetch())
            {

                $temp["topic_name"] = $topic_name;
                $temp["material_link"] = $material_link;
                $temp["material_type"] = $material_type;

                $json["DATA"][] = $temp;
                unset($temp);

               $counter++;
            }
        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get student weak topics";
        }
        

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }

/*
yeh kya kar rha hai?
*/
    public static function model_get_course_material($cid){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("SELECT topic.name, materialLink, type 
								FROM `coursematerial` JOIN topic on coursematerial.topicID = topic.ID 
								WHERE courseID = ?");  
        $sql->bind_param("i",$cid);


        $counter = 0;
        if ($sql->execute())
        {
            $json["STATUS"] = "SUCCESS";
            $sql->bind_result($topic_name,$material_link, $material_type);
            while ($sql->fetch())
            {

                $temp["topic_name"] = $topic_name;
                $temp["material_link"] = $material_link;
                $temp["material_type"] = $material_type;

                $json["DATA"][] = $temp;
                unset($temp);

               $counter++;
            }
        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get course material";
        }
        

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }





/*
The following function gets the courses assigned to a teacher
in the current semester to display on his profile.
*/ 
    public static function model_teacherCourses($teacher_id, $semester){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("SELECT offeredcourse.courseID, course.name 
								FROM `offeredcourse` JOIN course on offeredcourse.courseID = course.ID 
								WHERE teacherID = ? and semester = ? GROUP by course.ID");  
        $sql->bind_param("is",$teacher_id,$semester);

        $counter = 0;
        if ($sql->execute())
        {

            $sql->bind_result($course_id,$course_name);
            while ($sql->fetch())
            {

                $temp["course_id"] = $course_id;
                $temp["course_name"] = $course_name;
                

                $json["DATA"][] = $temp;
                unset($temp);

               $counter++;
            }
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "get teacher current semester courses Successfully";

        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get teacher courses";
        }
        

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }

/*
The following function retrieves a list of sections of a particular 
course that are being taught in the current semester.This shall also
be used to display to the teacher.
*/
    public static function model_getSections($cid, $semester){
    $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("SELECT name, section FROM `offeredcourse` WHERE courseID = ? AND semester= ?");  
        $sql->bind_param("is",$cid,$semester);

        $counter = 0;
        if ($sql->execute())
        {

            $sql->bind_result($name,$section);
            while ($sql->fetch())
            {

                $temp["course_name"] = $name;
                $temp["section"] = $section;
                

                $json["DATA"][] = $temp;
                unset($temp);

               $counter++;
            }
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "get a course sections Successfully";

        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get a course sections";
        }
        

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }




/*
The following function is used to obtain those assessments of a particular
student in the current semester in which he performed less than the threshold.
These assessments will be displayed in the feedback form.
*/
    public static function model_assessment4Feedback($student_id,$course_name){
            $conn = LearningAnalytics::establishConnection();
            $sql = $conn->prepare("SELECT assessment.ID, assessment.type
            FROM studentAssessment, assessment, offeredCourse
            WHERE studentAssessment.studentID = ?
            AND offeredCourse.name =  ?
            AND studentAssessment.assessmentID = assessment.ID
            AND offeredCourse.ID = assessment.offeredcourseID
            AND (
            studentAssessment.marksObtained / assessment.totalMarks
            ) < 0.5");  
            $sql->bind_param("is",$student_id,$course_name);


            $counter = 0;
            if ($sql->execute())
            {

                $sql->bind_result($assessment_id,$assessment_type);
                while ($sql->fetch())
                {

                    $temp["assessment_id"] = $assessment_id;
                    $temp["assessment_type"] = $assessment_type;

                    $json["DATA"][] = $temp;
                    unset($temp);

                   $counter++;
                }
                $json["STATUS"] = "SUCCESS";
                $json["MESSEGE"] = "Get assessments for feedback Successfully";
            }

            else
            {
                $json["STATUS"] = "FAIL";
                $json["MESSEGE"] = "Failed to get assessments needed for feedback";
            }
            

            $json["TotalRecords"] = $counter;
            $sql->close();
            mysqli_close($conn);
            return json_encode($json);
        }

/*
The following function gets assessments topics required for feedback.
*/
    public static function model_get_assessmentTopics($ass_id){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("SELECT topic.ID,topic.name 
								FROM topic,assessmentTopic 
								WHERE assessmentTopic.assessmentID=? AND assessmentTopic.topicID=topic.ID");  
        $sql->bind_param("i",$ass_id);


        $counter = 0;
        if ($sql->execute())
        {

            $sql->bind_result($topic_id,$topic_name);
            while ($sql->fetch())
            {

                $temp["topic_id"] = $topic_id;
                $temp["topic_name"] = $topic_name;

                $json["DATA"][] = $temp;
                unset($temp);

               $counter++;
            }
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "Get assessments topics Successfully";
        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get assessments topics";
        }
        

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }

    
/*
The following function saves the feedback provided by the student corresponding to 
the particular topic of an assessment.
*/
    public static function model_give_Feedback($student_id,$topic_id,$reason_id){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("insert into studentfeedback (studentID, topicID, reasonID) values (?,?,?)");  
        $sql->bind_param("iii",$student_id,$topic_id, $reason_id);


        if ($sql->execute() && mysqli_affected_rows($conn)>0)
        {

            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "Feedback submitted Successfully";
        }

        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to student feedback";
        }
        
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }

    //yeh kya kar rha hai? aur iss se oopar wala? dono main se konsa chalta hai?
    public static function saveFeedback($student_id, $topic_ids, $topic_names, $options1, $options2, $num_of_records, $ass_id, $courseID){
        $conn = LearningAnalytics::establishConnection();
                
        //echo($courseID);
        $num = 0;
        $i = 0;
        $my_query = "insert into topic_feedback (studentID, topicID, topic_name, was_tough, time_less, assessmentID, courseID) values (?,?,?,?,?,?,?)";
        $sql = $conn->prepare($my_query);  
        $sql->bind_param("iisssii",$student_id, $topic_ids, $topic_names, $options1, $options2, $ass_id, $courseID);
        
        if ($sql->execute() && mysqli_affected_rows($conn)>0)
        {
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "Feedback submitted Successfully";
        }
        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to student feedback";
        }
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }
    
/*
The following function is used to obtain the assessmentssaved in the 
feedback table.	//yeh kya kar rha hai?
*/
    public static function assessmentsInFeedback($student_id, $courseID){
        $conn = LearningAnalytics::establishConnection();
                
        $num = 0;
        $i = 0;
    
        $my_query = "select assessmentID from topic_feedback where studentID=? and courseID=?";
        $sql = $conn->prepare($my_query);  
        $sql->bind_param("ii",$student_id, $courseID);
        
        $counter = 0;
        if ($sql->execute())
        {

            $sql->bind_result($assessment_id);
            while ($sql->fetch())
            {

                $temp["assessment_id"] = $assessment_id;
                $json["DATA"][] = $temp;
                unset($temp);
               $counter++;
            }
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "Get assessments for feedback Successfully";
        }
        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get assessments needed for feedback";
        }
        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    
    }



/*
The following function gives us the top five most problematic topics of a course
offered in the current semester. It retrieves a list of the the topics from the 
offered courses in which most of the students performed less than the 
threshold. 
*/


    public static function model_ProblematicTopics($course_id){
        $conn = LearningAnalytics::establishConnection();

        $query = "SELECT DISTINCT topic.name, COUNT( * ) as no_of_students
                    FROM studentassessment, assessment, assessmenttopic, topic, offeredcourse
                    WHERE studentassessment.assessmentID = assessment.ID
                    AND offeredcourse.ID = assessment.offeredcourseID
                    AND assessmenttopic.assessmentID = assessment.ID
                    AND assessmenttopic.topicID = topic.ID
                    AND (
                            studentassessment.marksObtained / assessment.totalMarks
                        ) < 0.5
                    GROUP BY topic.name
                    ORDER by no_of_students DESC
                    LIMIT 5";       // give top five topics

        //storing the result of the executed query
        $result = $conn->query($query);

        //initialize the array to store the processed data
        $jsonArray = array();

        //check if there is any data returned by the SQL Query
        if ($result->num_rows > 0) {
          //Converting the results into an associative array
          while($row = $result->fetch_assoc()) {
            $jsonArrayItem = array();
            $jsonArrayItem['label'] = $row['name'];
            $jsonArrayItem['value'] = $row['no_of_students'];
            //append the above created object into the main array.
            array_push($jsonArray, $jsonArrayItem);
          }
        }

        //Closing the connection to DB
        $conn->close();

        //set the response content type as JSON
        header('Content-type: application/json');
        //output the return value of json encode using the echo function. 
        echo json_encode($jsonArray);
    }

/*
The following function returns the  weak topics of a section corresponding to number
of students and course. The results are displayed as a report for the teacher.
*/
    
    public static function weakTopicReport($course_id, $section){
        $conn = LearningAnalytics::establishConnection();


        $query = "SELECT DISTINCT topic.name, COUNT( * ) as no_of_students
                    FROM studentassessment, assessment, assessmenttopic, topic, offeredcourse
                    WHERE studentassessment.assessmentID = assessment.ID
                    AND offeredcourse.ID = assessment.offeredcourseID
                    AND assessmenttopic.assessmentID = assessment.ID
                    AND assessmenttopic.topicID = topic.ID
                    AND offeredcourse.courseID='$course_id'
                    AND offeredcourse.section='$section'
                    AND (
                    studentassessment.marksObtained / assessment.totalMarks
                    ) < 0.5
                    GROUP BY topic.name";

        //storing the result of the executed query
        $result = $conn->query($query);

        //initialize the array to store the processed data
        $jsonArray = array();

        //check if there is any data returned by the SQL Query
        if ($result->num_rows > 0) {
          //Converting the results into an associative array
          while($row = $result->fetch_assoc()) {
            $jsonArrayItem = array();
            $jsonArrayItem['label'] = $row['name'];
            $t_name=$row['name'];
            $jsonArrayItem['value'] = $row['no_of_students'];
            $jsonArrayItem['link'] = "j-showAlert-'$t_name'";
            //append the above created object into the main array.
            array_push($jsonArray, $jsonArrayItem);
          }
        }

        //Closing the connection to DB
        $conn->close();

        //set the response content type as JSON
        header('Content-type: application/json');
        //output the return value of json encode using the echo function. 
        echo json_encode($jsonArray);

    }

	
/*
The following function gives data for the drill-down report of a student.It retrievess
the data of the student who has performed poor topic-wise in a current section of an
offered course.
*/
    public static function topicwise_studentsDetail($course_id, $section, $topic_name){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare(("SELECT DISTINCT studentassessment.studentID, student.name, student.batch 
								FROM studentassessment, assessment, assessmenttopic, topic, offeredcourse, student 
								WHERE studentassessment.assessmentID = assessment.ID 
								AND offeredcourse.ID = assessment.offeredcourseID 
								AND assessmenttopic.assessmentID = assessment.ID 
								AND assessmenttopic.topicID = topic.ID 
								AND studentassessment.studentID = student.studentID 
								AND offeredcourse.courseID =? 
								AND offeredcourse.section = ? AND topic.name = ? 
								AND ( studentassessment.marksObtained / assessment.totalMarks ) < 0.5"));  
        
		$sql->bind_param("iss",$course_id,$section, $topic_name);

        if ($sql->execute())
        {
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "get student registered courses Successfully";
        }
        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get student registered courses";
        }
        $counter = 0;
        $sql->bind_result($sid, $sname,$batch);
        while ($sql->fetch())
        {
            $temp["student_id"] = $sid;
            $temp["student_name"] = $sname;
            $temp["batch"] = $batch;

            $json["DATA"][] = $temp;
            unset($temp);

            $counter++;
        }
        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }

/*
this function returns no OF Students within a range of Predicted marks
to display as a report.
*/

    public static function model_Predicted_marks_report($cid,$tid,$section){
        $conn = LearningAnalytics::establishConnection();
        $query = "SELECT registeredcourse.predictedGrade AS 'predicted_grade' , COUNT( * ) AS 'number_of_users' FROM registeredCourse,offeredCourse WHERE registeredCourse.offeredCourseID=offeredCourse.ID AND offeredCourse.teacherID='$tid' AND offeredCourse.courseID='$cid' AND offeredcourse.section='$section' GROUP BY registeredcourse.predictedGrade";
        //storing the result of the executed query
        $result = $conn->query($query);

        //initialize the array to store the processed data
        $jsonArray = array();

        //check if there is any data returned by the SQL Query
        if ($result->num_rows > 0) {
          //Converting the results into an associative array
          while($row = $result->fetch_assoc()) {
            $jsonArrayItem = array();
            $jsonArrayItem['label'] = $row['predicted_grade'];
            $r_value=$row['predicted_grade'];
            $jsonArrayItem['value'] = $row['number_of_users'];
            $jsonArrayItem['link'] = "j-showAlert2-'$r_value'";
            //append the above created object into the main array.
            array_push($jsonArray, $jsonArrayItem);
          }
        }

        //Closing the connection to DB
        $conn->close();

        //set the response content type as JSON
        header('Content-type: application/json');
        //output the return value of json encode using the echo function. 
        echo json_encode($jsonArray);
    }

/*
The following function gives details of students w.r.t their marks.
*/

    public static function Markswise_studentsDetail($course_id, $section, $rgrade){
        $conn = LearningAnalytics::establishConnection();
        $sql = $conn->prepare("SELECT DISTINCT student.studentID,student.name, student.batch, registeredcourse.predictedGrade
                                FROM offeredcourse JOIN registeredcourse JOIN student
                                ON offeredcourse.ID=registeredcourse.offeredCourseID
                                AND registeredcourse.studentID=student.studentID
                                WHERE offeredcourse.courseID =? 
                                AND offeredcourse.section = ? 
                                AND registeredcourse.predictedGrade= ?
                                GROUP by student.studentID");  
        $sql->bind_param("iss",$course_id,$section, $rgrade);

        if ($sql->execute())
        {
            $json["STATUS"] = "SUCCESS";
            $json["MESSEGE"] = "get student registered courses Successfully";

            $counter = 0;
            $sql->bind_result($sid, $sname,$batch, $prediction);
            while ($sql->fetch())
            {
                $temp["student_id"] = $sid;
                $temp["student_name"] = $sname;
                $temp["batch"] = $batch;
                $temp["predictedMarks"] = $prediction;

                $json["DATA"][] = $temp;
                unset($temp);

                $counter++;
            }
        }
        else
        {
            $json["STATUS"] = "FAIL";
            $json["MESSEGE"] = "Failed to get student registered courses";
        }

        $json["TotalRecords"] = $counter;
        $sql->close();
        mysqli_close($conn);
        return json_encode($json);
    }

}
?>