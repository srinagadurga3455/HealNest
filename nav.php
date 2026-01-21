<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>this is navbar</h1>
    <form action="" method="post">
        <label for="name" >Name:</label>
        <input type="text" id="name" name="name">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password">

        <input type="submit" value="Submit">

    </form>

    <h1>Waste fellow data</h1>
    // display data from student table
    <?php
    include 'connect.php';
    $sql = "SELECT id, name, email, password FROM student";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            echo "id: " . $row["id"]. " - Name: " . $row["name"]. " - Email: " . $row["email"]. " - Password: " . $row["password"]. "<br>";
        }
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>
</body>

</html>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = ($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);

    // Here you can process the data, e.g., save it to a database or send an email
    echo "Form submitted successfully!<br>";

    // use the connect,php file send  the above data to database student table
    include 'connect.php';
    $sql = "INSERT INTO student (name, email, password) VALUES ('$name', '$email', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}
?>