<?php
$sql = "SELECT f.*, u.username FROM files f 
        JOIN users u ON f.uploaded_by = u.id 
        WHERE is_hidden = 0 OR uploaded_by = ?
        ORDER BY created_at DESC";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) > 0) {
        echo "<table class='file-list'>";
        echo "<tr><th>File Name</th><th>Uploaded By</th><th>Date</th><th>Actions</th></tr>";
        
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row["filename"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
            echo "<td>
                    <a href='../includes/download.php?id=" . $row["id"] . "'>Download</a>
                    " . ($row["uploaded_by"] == $_SESSION["id"] ? 
                    "<a href='../includes/delete.php?id=" . $row["id"] . "'>Delete</a>" : "") . "
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No files found.</p>";
    }
}
?>
