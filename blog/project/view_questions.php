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

// Handle form submission for answering a question
if(isset($_POST['answer']) && isset($_POST['question_id'])){
   // Retrieve form data
   $question_id = $_POST['question_id'];
   $answer = $_POST['answer'];
   
   // Sanitize form data
   $answer = filter_var($answer, FILTER_SANITIZE_STRING);
   
   // Insert answer into question_answers table
   $insert_answer = $conn->prepare("INSERT INTO `question_answers` (`question_id`, `user_id`, `answer`) VALUES (?, ?, ?)");
   $insert_answer->execute([$question_id, $user_id, $answer]);
   
   // Check if answer was inserted successfully
   if($insert_answer->rowCount() > 0){
      $message[] = 'Answer inserted successfully.';
   } else {
      $message[] = 'Error inserting answer.';
   }
}

// Retrieve all questions and their associated answers from the quora and question_answers tables
$select_questions = $conn->query("SELECT quora.*, question_answers.answer FROM quora LEFT JOIN question_answers ON quora.id = question_answers.question_id ORDER BY quora.date DESC");

// Fetch questions along with their answers
$questions = [];
while ($row = $select_questions->fetch(PDO::FETCH_ASSOC)) {
    $question_id = $row['id'];
    // If the question is not yet in the $questions array, add it
    if (!isset($questions[$question_id])) {
        $questions[$question_id] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'question' => $row['question'],
            'date' => $row['date'],
            'answers' => [] // Initialize answers array
        ];
    }
    // If an answer exists for the question, add it to the answers array
    if ($row['answer']) {
        $questions[$question_id]['answers'][] = $row['answer'];
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
   <style>
/* Additional CSS styles for this page */
.form-container {
   margin: 20px auto;
   padding: 20px;
   max-width: 600px;
   border: 1px solid #ccc;
   border-radius: 5px;
   background-color: #f9f9f9;
}

.question-card {
   margin-bottom: 20px;
   padding: 20px;
   border: 1px solid #ccc;
   border-radius: 5px;
   background-color: #fff;
}

.question-card h3 {
   color: #333;
   font-size: 24px;
   margin-bottom: 10px;
}

.question-card p {
   margin-bottom: 10px;
   font-size: 18px;
}

.question-card .date {
   color: #777;
   font-size: 14px;
}

.question-card form textarea {
   margin-bottom: 10px;
   width: 100%;
   padding: 10px;
   border-radius: 10px;
   border: 1px solid #ccc;
   resize: vertical;
}

.question-card form button[type="submit"] {
   background-color: #4834d4;
   color: #fff;
   border: none;
   border-radius: 5px;
   padding: 12px 24px;
   cursor: pointer;
   font-size: 16px;
}

.question-card form button[type="submit"]:hover {
   background-color: #0056b3;
}
.question-card .answers {
   font-size: 20px; /* Adjust the font size of the answers */
}
ul {
  list-style-type: square;
}

.question-card .answers li {
   font-size: 20px; /* Adjust the font size of each answer */
}
.question-card ul {
    margin-top: 20px; /* Adjust the margin as needed */
}
.question-card ul li {
    margin-bottom: 10px; /* Adjust the margin as needed */
}
.question-card h4 {
  margin-top: 0; /* Remove any default margin */
  margin-bottom: 10px; /* Adjust as needed */
}

.question-card ul {
  margin-top: 0; /* Remove default margin */
  margin-left: 20px; /* Adjust the left margin to align with the answers heading */
}



/* CSS for the button container */
.button-container {
   text-align: center;
   margin-top: 20px;
}

.button-container button {
   margin: 0 10px;
   padding: 12px 24px;
   border: none;
   border-radius: 5px;
   background-color: #4834d4;
   color: #fff;
   font-size: 16px;
   cursor: pointer;
}

.button-container button:hover {
   background-color: #0056b3;
}

/* Responsive styles */
@media only screen and (max-width: 768px) {
   .form-container {
      max-width: 90%;
   }

   .question-card {
      padding: 20px; /* Adjust padding for smaller screens */
   }

   .question-card h3 {
      font-size: 24px; /* Decrease font size for question title */
      margin-bottom: 15px; /* Decrease margin */
   }

   .question-card p {
      font-size: 18px; /* Decrease font size for question */
      margin-bottom: 15px; /* Decrease margin */
   }

   .question-card .date {
      font-size: 14px; /* Decrease font size for date */
   }

   .question-card form textarea {
      font-size: 14px; /* Decrease font size for textarea */
   }

   .question-card form button[type="submit"] {
      padding: 12px 24px; /* Adjust padding */
      font-size: 16px; /* Keep font size */
   }

   .button-container button {
      padding: 12px 24px; /* Adjust padding */
      font-size: 16px; /* Keep font size */
   }
}

</style>
</head>
<body>

<section class="questions-container">
   <?php if (!empty($questions)) : ?>
      <?php foreach ($questions as $question) : ?>
         <div class="question-card">
            <h3><?php echo $question['title']; ?></h3>
            <p><?php echo $question['question']; ?></p>
            <p class="date"><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($question['date'])); ?></p>
            <!-- Display answers -->
            <?php if (!empty($question['answers'])) : ?>
               <h4 class="answers">Answers:</h4>
               <ul>
                  <?php foreach ($question['answers'] as $answer) : ?>
                     <li class="answers"><?php echo $answer; ?></li>
                  <?php endforeach; ?>
               </ul>
            <?php endif; ?>
            <!-- Form to answer the question -->
            <form method="post">
               <textarea name="answer" placeholder="Type your answer here..." rows="4" cols="50"></textarea><br>
               <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>"> <!-- Correctly set the question_id -->
               <button type="submit">Submit Answer</button>
            </form>
         </div>
      <?php endforeach; ?>
   <?php else : ?>
      <p>No questions asked yet.</p>
   <?php endif; ?>
</section>
 <?php include 'components/footer.php'; ?>

<section class="button-container">
    <button onclick="window.location.href = 'home.php'">Home</button>
    <button onclick="window.location.href = 'quora_question.php'">Ask Question</button>
</section>

</body>
</html>
