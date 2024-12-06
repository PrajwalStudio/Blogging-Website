<?php
// Include database connection and session start
include 'components/connect.php';
session_start();

// Check if user is logged in and get user_id
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

// Handle form submission for asking a question
if(isset($_POST['ask_question'])){
   // Retrieve form data
   $title = $_POST['title'];
   $question = $_POST['question'];
   
   // Sanitize form data
   $title = filter_var($title, FILTER_SANITIZE_STRING);
   $question = filter_var($question, FILTER_SANITIZE_STRING);
   
   // Insert question into quora table
   $insert_question = $conn->prepare("INSERT INTO `quora` (`user_id`, `title`, `question`) VALUES (?, ?, ?)");
   $insert_question->execute([$user_id, $title, $question]);
   
   // Check if question was inserted successfully
   if($insert_question->rowCount() > 0){
      $message[] = 'Question inserted successfully.';
   } else {
      $message[] = 'Error inserting question.';
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Ask a Question</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
   <!-- Include header section -->
   <?php include 'components/user_header.php'; ?>
   
   <section class="form-container">
      <form action="" method="post">
         <h3>Ask a Question</h3>
         <input type="text" name="title" required placeholder="Enter the title" class="box" maxlength="100">
         <textarea name="question" required placeholder="Enter your question" class="box" maxlength="1000"></textarea>
         <input type="submit" value="Ask Question" name="ask_question" class="btn">
         <!-- Button to view questions and answers -->
         <input type="button" value="View Questions and Answers" onclick="window.location.href='view_questions.php'" class="btn">
      </form>
   </section>
   
   <!-- Include footer section -->
   <?php include 'components/footer.php'; ?>
   
   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>
</html>

