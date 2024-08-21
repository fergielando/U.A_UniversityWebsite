<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIS Department Message</title>
    <link rel="stylesheet" href="css/fatman1.css"> <!-- Include your stylesheet here -->
    <style>
        /* Add your custom styles here */
    

        .message-container {
            margin: 0 auto; /* Center the container horizontally */
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .message-container h2 {
            font-size: 24px; /* Slightly smaller font size for the message title */
        }

        .message-container p {
            font-size: 18px; /* Smaller font size for the message content */
            line-height: 1.6; /* Add spacing between lines */
            margin-bottom: 10px; /* Add spacing between paragraphs */
        }

        .department-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .department-table th, .department-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .header {
            background: #000;
            color: #fff;
            padding: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
        }

        .header h1 {
            font-size: 36px;
        }

        .container {
            margin-top: 20px;
        }

        .buttons {
            display: flex;
            align-items: center;
        }

        .buttons a {
            margin-left: 20px;
            background: #000;
            color: #fff;
            padding: 10px 30px;
            text-decoration: none;
            border-radius: 5px;
        }

        .buttons .btn:hover {
            background: #333;
        }

        .header .logo {
            width: 50px;
            height: 50px;
        }
    </style>
</head>
<body>
<div class="header">
    <img src="ua.png" alt="U.A. Logo" class="logo">
    <h1>U.A. University Admin Page</h1>
    <div class="buttons">
        <a href="logout1.php" class="btn">Logout</a>
        <a href="Create_a_user1.php" class="btn">Create a user</a>
        <a href="Update_a_user1.php" class="btn">Update a user</a>
        <a href="Departments_page1.php" class="btn">Departments</a>
    </div>
</div>
<div class="message-container">
    <h2> Dear Members of the MIS field,</h2>
    <p>
       

        I hope this message finds you well. I wanted to take a moment to express my appreciation for the hard work and dedication that each of you brings to the MIS department at U.A. University. Your contributions are invaluable, and your commitment to excellence is truly commendable.
        The field of Management Information Systems plays a crucial role in our university's operations, helping us manage data, technology, and information effectively. Your expertise is at the heart of our university's success, ensuring that our systems run smoothly and that we make data-driven decisions.
        As we continue to navigate the challenges and opportunities in the ever-evolving world of technology and information management, your knowledge and innovation remain instrumental. Your contributions not only advance the department but also benefit the entire university community.
        I want to encourage you to keep up the fantastic work and continue exploring new horizons within MIS. Your ideas and efforts drive progress, and we look forward to seeing the department thrive under your leadership.
        If you ever have suggestions, ideas, or concerns, please don't hesitate to reach out. Your feedback is valuable, and I am here to support you in any way I can.
        Thank you again for your exceptional work and dedication to the MIS department. Together, we will continue to achieve new heights of excellence.

        <p>Warm regards,</p>

        <p>Bryan Don<br>Dept Manager<br>U.A. University</p>
    </p>
</div>
<table class="department-table">
    <tr>
       
        <th>Department Manager</th>
        <th>Email Address</th>
        <th>Phone Number</th>
        <th>Room</th>
    </tr>
    <tr>
        
        <td>Bryan Don</td>
        <td>bryan.don@UAuni.edu</td>
        <td>(555) 123-4567</td>
        <td>1</td>
    </tr>
</table>
</body>
</html>
