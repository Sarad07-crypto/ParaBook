<?php

class Connect extends PDO
{
    public function __construct()
    {
        parent::__construct(
            "mysql:host=localhost:3307;dbname=parabook",
            'root',
            'parabook',
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
    }
}

class Controller
{

    function printUsers($id)
    {
        $db = new Connect;
        $user = $db->prepare("SELECT * FROM users ORDER BY id ");
        $user->execute();
        $content = '
                <table class="table" style="width:100%; height:100px;text-align:center;">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">First Name</th>
                            <th scope="col">Last Name</th>
                            <th scope="col">Avatar</th>
                            <th scope="col">Email</th>
                        </tr>
                    </thead>
                    <tbody>
            ';

        while ($userInfo = $user->fetch(PDO::FETCH_ASSOC)) {

            $content .= '
                    <tr>
                        <td>' . htmlspecialchars($userInfo["givenName"]) . '</td>
                        <td>' . htmlspecialchars($userInfo["familyName"]) . '</td>
                        <td><img src="' . htmlspecialchars($userInfo["avatar"]) . '" alt="avatar" 
                        style="
                            width:40px;
                            height:40px;
                        "></td>
                        <td>' . htmlspecialchars($userInfo["email"]) . '</td>
                    </tr>
                ';
        }

        $content .= '</tbody></table>';
        return $content;
    }

    function insertData($data)
    {

        try {
            $db = new Connect();
            $session = bin2hex(random_bytes(16));

            $checkMainUser = $db->prepare("SELECT * FROM users WHERE email = :email");
            $checkMainUser->execute([':email' => $data['email']]);
            $mainUser = $checkMainUser->fetch(PDO::FETCH_ASSOC);

            $checkUser = $db->prepare("SELECT id FROM users WHERE google_id = :g_id");
            $checkUser->execute([":g_id" => $data["g_id"]]);
            $existingUser = $checkUser->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {

                $userId = $existingUser['id'];

                $updateSession = $db->prepare("
                        UPDATE users_sessions
                        SET session_token = :session, created_at = NOW()
                        WHERE user_id = :user_id
                    ");
                $updateSession->execute([
                    ":user_id" => $userId,
                    ":session" => $session
                ]);

                // Set cookies
                setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/", NULL);
                setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/", NULL);

                header("Location: ../home/passenger.php");
                exit();
            }

            if ($mainUser) {
                session_destroy();
                echo "<script>alert('User already registered.'); window.location.href='/ParaBook/Web/php/login_signup/login.php';</script>";
                exit();
            }

            // Insert into users
            $insertUser = $db->prepare("
                    INSERT INTO users (email, password, google_id, facebook_id, sign_with)
                    VALUES (:email, NULL, :g_id, NULL, :sign_with)
                ");
            $insertUser->execute([
                ":email" => $data["email"],
                ":g_id" => $data["g_id"],
                ":sign_with" => $data["sign_with"]
            ]);

            $userId = $db->lastInsertId();

            // Insert into users_info
            $insertInfo = $db->prepare("
                    INSERT INTO users_info (
                        user_id, acc_type, firstName, lastName, gender, contact, dob, country, avatar
                    ) VALUES (
                        :user_id, NULL, :f_Name, :l_Name, NULL, NULL, NULL, NULL, :avatar
                    )
                ");
            $insertInfo->execute([
                ":user_id" => $userId,
                ":f_Name" => $data["givenName"],
                ":l_Name" => $data["familyName"],
                ":avatar" => $data["avatar"]
            ]);

            // Insert verification
            $insertverify = $db->prepare("
                    INSERT INTO users_verify (user_id, verify_token, is_verified, otp, otp_expiry)
                    VALUES (:user_id, NULL, 1, NULL, NULL)
                ");
            $insertverify->execute([
                ":user_id" => $userId
            ]);

            // Insert session
            $insertSession = $db->prepare("
                    INSERT INTO users_sessions (user_id, session_token, created_at)
                    VALUES (:user_id, :session, NOW())
                ");
            $insertSession->execute([
                ":user_id" => $userId,
                ":session" => $session
            ]);

            // Set cookies
            setcookie("id", $userId, time() + 60 * 60 * 24 * 30, "/", NULL);
            setcookie("session", $session, time() + 60 * 60 * 24 * 30, "/", NULL);

            echo "<script>alert('User successfully registered.'); window.location.href='ParaBook/Web/php/home/passenger.php';</script>";
            exit();

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
?>