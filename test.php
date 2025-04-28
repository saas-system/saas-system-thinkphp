<?php
$password = '123456';
$newPassword = hash_password($password, PASSWORD_DEFAULT);
echo $newPassword;



function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

echo "<br/>";
exit;