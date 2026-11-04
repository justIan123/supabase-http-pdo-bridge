<?php

require '../config/db_supabase_init.php';

$message = '';

// CREATE
if (isset($_POST['create'])) {

    $stmt = $pdo->prepare("
        INSERT INTO table_test (
            name,
            email,
            phone_number,
            age
        )
        VALUES (
            :name,
            :email,
            :phone_number,
            :age
        )
    ");

    $stmt->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':phone_number' => $_POST['phone_number'],
        ':age' => $_POST['age'],
    ]);

    $message = "Record added!";
}

// DELETE
if (isset($_GET['delete'])) {

    $stmt = $pdo->prepare("
        DELETE FROM table_test
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $_GET['delete']
    ]);

    $message = "Record deleted!";
}

// UPDATE
if (isset($_POST['update'])) {

    $stmt = $pdo->prepare("
        UPDATE table_test
        SET
            name = :name,
            email = :email,
            phone_number = :phone_number,
            age = :age
        WHERE id = :id
    ");

    $stmt->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':phone_number' => $_POST['phone_number'],
        ':age' => $_POST['age'],
        ':id' => $_POST['id']
    ]);

    $message = "Record updated!";
}

// FETCH RECORDS
$stmt = $pdo->prepare("
    SELECT *
    FROM table_test
    ORDER BY id DESC
");

$stmt->execute();

$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// EDIT MODE
$edit = null;

if (isset($_GET['edit'])) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM table_test
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $_GET['edit']
    ]);

    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>PDO CRUD Test</title>
    <style>

        body{
            font-family: Arial;
            max-width: 900px;
            margin: 30px auto;
        }

        input{
            padding:8px;
            margin:5px;
        }

        button{
            padding:8px 15px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        th,td{
            border:1px solid #ccc;
            padding:10px;
            text-align:left;
        }

    </style>
</head>
<body>

<h2>Simple PDO CRUD Test</h2>

<?php if($message): ?>
    <p><b><?= $message ?></b></p>
<?php endif; ?>

<form method="post">

    <input
        type="hidden"
        name="id"
        value="<?= $edit['id'] ?? '' ?>"
    >

    <input
        type="text"
        name="name"
        placeholder="Name"
        value="<?= $edit['name'] ?? '' ?>"
        required
    >

    <input
        type="email"
        name="email"
        placeholder="Email"
        value="<?= $edit['email'] ?? '' ?>"
        required
    >

    <input
        type="text"
        name="phone_number"
        placeholder="Phone Number"
        value="<?= $edit['phone_number'] ?? '' ?>"
    >

    <input
        type="number"
        name="age"
        placeholder="Age"
        value="<?= $edit['age'] ?? '' ?>"
    >

    <?php if($edit): ?>

        <button type="submit" name="update">
            Update
        </button>

        <a href="index.php">
            Cancel
        </a>

    <?php else: ?>

        <button type="submit" name="create">
            Add Record
        </button>

    <?php endif; ?>

</form>

<table>

    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Age</th>
        <th>Actions</th>
    </tr>

    <?php foreach($records as $row): ?>

        <tr>

            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone_number']) ?></td>
            <td><?= $row['age'] ?></td>

            <td>

                <a href="?edit=<?= $row['id'] ?>">
                    Edit
                </a>

                |

                <a
                    href="?delete=<?= $row['id'] ?>"
                    onclick="return confirm('Delete this record?')"
                >
                    Delete
                </a>

            </td>

        </tr>

    <?php endforeach; ?>

</table>

</body>
</html>