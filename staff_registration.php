<?php
$pagetitle = "Staff Registration";
require_once('assets/header.php');
require_once('assets/db_connect.php');
require_once('assets/mailer.php');

$fname = $mname = $lname = $email = $mobile = $dob = $password = $cpassword = $address = $gender = $nin = $bvn = $nationality = $soo = $lgoo = $mmn = $hashPass = "";
$msg = $mobileError = $passError = $cpassError = $ninError = $bvnError = $emailError = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $nin = $_POST['nin'];
    $bvn = $_POST['bvn'];
    $nationality = $_POST['nationality'];
    $soo = $_POST['soo'];
    $lgoo = $_POST['lgoo'];
    $mmn = $_POST['mmn'];

    // E-mail verification
    $query = "SELECT * FROM staffs WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $emailError = "Email address already exists";
    }

    // Mobile verification
    if (!preg_match('/^(0|234|\+234)[789][01]\d{8}$/', $mobile)) {
        $mobileError = "Invalid Phone Number Format";
    } else {
        $query = "SELECT * FROM staffs WHERE phone_number = ?";
        $stmt = mysqli_prepare($conn, $query);
        $stmt->bind_param('s', $mobile);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $mobileError = "Phone number already exists";
        }
    }

    // Password Verification
    if ($password !== $cpassword) {
        $passError = $cpassError = "Passwords do not match";
    } else {
        $hashPass = password_hash($password, PASSWORD_DEFAULT);
    }

    // NIN Verification
    if (!preg_match('/\d{16}/', $nin)) {
        $ninError = "Invalid NIN entered";
    } else {
        $query = "SELECT * FROM staffs WHERE nin = ?";
        $stmt = mysqli_prepare($conn, $query);
        $stmt->bind_param('s', $nin);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $ninError = "NIN already exists";
        }
    }

    // BVN Verification
    if (!preg_match('/\d{10}/', $bvn)) {
        $bvnError = "Invalid BVN entered";
    } else {
        $query = "SELECT * FROM staffs WHERE bvn = ?";
        $stmt = mysqli_prepare($conn, $query);
        $stmt->bind_param('s', $bvn);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $bvnError = "BVN already exists";
        }
    }

    // Database Population
    if ($mobileError ==  "" && $passError == "" && $cpassError == "" && $ninError == "" && $bvnError == "" && $emailError == "") {
        $query = "INSERT INTO staffs(firstname, middlename, surname, email, password, phone_number, date_of_birth, gender, home_address, nin, bvn, nationality, mother_maiden_name, state_of_origin, lga_of_origin) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssssssssssssss', $fname, $mname, $lname, $email, $hashPass, $mobile, $dob, $gender, $address, $nin, $bvn, $nationality, $mmn, $soo, $lgoo);
        if ($stmt->execute()) {
            $code = rand(100000, 999999);
            // From current time
            $date = new DateTime();
            $date->setTimezone(new DateTimeZone('Africa/Lagos'));
            // Add 30 minutes to the current time
            $expired = $date->modify('+30 minutes')->format('Y-m-d H:i:s');

            $stmt1 = $conn->prepare("INSERT INTO account_verifications(email, code, user_type, expired_at) VALUES (?, ?, 'staff', ?)");
            $stmt1->bind_param('sss', $email, $code, $expired);
            if ($stmt1->execute()) {
                $fullname = $fname . " " . $mname . " " . $lname;
                $link = "http://localhost/orplebank/staff_verification.php?email=" . urlencode($email) . "&code=" . urlencode($code);
                // Content
                $mail->isHTML(true);        
                $mail->addAddress($email, $fullname); // Set email format to HTML
                $mail->Subject = 'Account Verification';
                $mail->Body    = "<h1>Account Verification</h1><p>Dear $fullname,</p><p>Your staff account has been created with Orple Bank. To complete your registration, please use the following verification code:</p><h2>$code</h2><p>or click the link below:</p><p><a href='$link'>Verify Account</a></p><p>This code will expire in 30 minutes.</p><p>Best regards,<br/>Orple Bank Team</p>";
                $mail->AltBody = "Dear $fullname,\n\nYour staff account has been created with Orple Bank. To complete your registration, please use the following verification code: $code\n\nor click the link below:\n$link\n\nThis code will expire in 30 minutes.\n\nBest regards,\nOrple Bank Team";

                if($mail->send()) {
                    $msg = "<span class='text-green-600'>Register Successfully</span>";
                }
            }
        } else {
            $msg = "<span class='text-red-600'> Registration Failed</span>";
        }
    } else {
        $msg = "<span class='text-red-600'> Registration Failed</span>";
    }
}
?>

<main class="min-h-screen flex flex-col justify-center p-4 md:p-8">
    <div class="w-full max-w-lg mx-auto sm:max-w-4xl">
        <div class="mb-12">
            <a href="#">
                <img src="https://readymadeui.com/readymadeui.svg" alt="logo" class="w-40 inline-block dark:invert dark:brightness-100" />
            </a>

            <p class="text-slate-600 text-base mt-6 dark:text-slate-400">Create your account and get started</p>
        </div>

        <form class="w-full" enctype="multipart/form-data" method="post">
            <div>
                <h1 class="text-3xl text-bold flex flex-col items-center justify-center"><?= $msg ?></h1>
            </div>
            <!-- Image Upload -->
            <div>
                <label for="uploadFile1" class="bg-white text-slate-600 font-semibold text-sm rounded-md max-w-sm h-48 flex flex-col items-center justify-center cursor-pointer border border-slate-300 mx-auto mt-6 focus-within:ring-2 focus-within:ring-blue-500 dark:bg-neutral-900 dark:text-slate-300 dark:border-neutral-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-10 mb-4 fill-gray-400" viewBox="0 0 32 32" aria-hidden="true">
                        <path
                            d="M23.75 11.044a7.99 7.99 0 0 0-15.5-.009A8 8 0 0 0 9 27h3a1 1 0 0 0 0-2H9a6 6 0 0 1-.035-12 1.038 1.038 0 0 0 1.1-.854 5.991 5.991 0 0 1 11.862 0A1.08 1.08 0 0 0 23 13a6 6 0 0 1 0 12h-3a1 1 0 0 0 0 2h3a8 8 0 0 0 .75-15.956z"
                            data-original="#000000" />
                        <path
                            d="M20.293 19.707a1 1 0 0 0 1.414-1.414l-5-5a1 1 0 0 0-1.414 0l-5 5a1 1 0 0 0 1.414 1.414L15 16.414V29a1 1 0 0 0 2 0V16.414z"
                            data-original="#000000" />
                    </svg>

                    <div>
                        <p class="text-slate-400 font-semibold text-sm">Drag & Drop or <span class="text-blue-700">Choose file</span> to
                            upload</p>
                        <p class="text-xs font-normal text-slate-400 text-center mt-2">PNG, JPG SVG, WEBP, and GIF are Allowed.</p>
                    </div>

                    <input type="file" id='uploadFile1' class="sr-only" />
                </label>
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
                <!-- Firstname -->
                <div>
                    <label for="fname" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">First
                        Name</label>
                    <input type="text" id="fname" name="fname" placeholder="John" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $fname ?>" />
                </div>

                <!-- Middlename -->
                <div>
                    <label for="mname" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Middle
                        Name</label>
                    <input type="text" id="mname" name="mname" placeholder="Smith" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $mname ?>" />

                </div>

                <!-- lastname -->
                <div>
                    <label for="lname" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Last
                        Name</label>
                    <input type="text" id="lname" name="lname" placeholder="Doe" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $lname ?>" />
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email"
                        class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Email</label>
                    <input type="email" id="email" name="email" placeholder="john@readymadeui.com" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $email ?>" />
                    <span class="text-red-600"><?= $emailError ?></span>
                </div>

                <!-- Mobile -->
                <div>
                    <label for="mobile"
                        class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" placeholder="123-456-7890" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $mobile ?>" />

                    <span class="text-red-600"><?= $mobileError ?></span>
                </div>

                <!-- Date Of Birth -->
                <div>
                    <label for="dob" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Date Of Birth</label>
                    <input type="date" id="dob" name="dob" placeholder="Doe" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $dob ?>" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password"
                        class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $password ?>" />
                    <span class="text-red-600"><?= $passError ?></span>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="cpassword"
                        class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Confirm
                        Password</label>
                    <input type="password" id="cpassword" name="cpassword" placeholder="••••••••" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $cpassword ?>" />
                    <span class="text-red-600"><?= $cpassError ?></span>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Home Address</label>
                    <input type="text" id="address" name="address" placeholder="123, Ray Close, Lagos" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $address ?>" />
                </div>

                <!-- gender -->
                <div>
                    <label for="gender" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Gender</label>
                    <select id="gender" name="gender" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $gender ?>">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="others">Others</option>
                    </select>
                </div>

                <!-- NIN -->
                <div>
                    <label for="nin" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">NIN</label>
                    <input type="text" id="nin" name="nin" placeholder="1234567890765412" maxlength="16" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $nin ?>" />
                    <span class="text-red-600"><?= $ninError ?></span>
                </div>

                <!-- BVN -->
                <div>
                    <label for="bvn" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">BVN</label>
                    <input type="text" id="bvn" name="bvn" placeholder="7890765412" maxlength="10" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $bvn ?>" />
                    <span class="text-red-600"><?= $bvnError ?></span>
                </div>

                <!-- Nationality -->
                <div>
                    <label for="natioanality" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Nationality</label>
                    <input type="text" id="nationality" name="nationality" placeholder="Nigerian" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $nationality ?>" />
                </div>

                <!-- State Of Origin -->
                <div>
                    <label for="soo" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">State Of Origin</label>
                    <input type="text" id="soo" name="soo" placeholder="Lagos" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $soo ?>" />
                </div>

                <!-- Local Government Of Origin -->
                <div>
                    <label for="lgoo" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Local Government Of Origin</label>
                    <input type="text" id="lgoo" name="lgoo" placeholder="Ajeromi-Ifelodun" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $lgoo ?>" />
                </div>

                <!-- Mother's Maiden Name -->
                <div>
                    <label for="mmn" class="mb-2 text-slate-900 font-medium text-sm inline-block dark:text-slate-50">Mother's Maiden Name</label>
                    <input type="text" id="mmn" name="mmn" placeholder="Jane" required
                        class="px-3 py-2.5 text-sm text-slate-900 rounded-md bg-white w-full outline-1 -outline-offset-1 outline-slate-300 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 dark:text-slate-50 dark:bg-neutral-800 dark:outline-neutral-700" value="<?= $mmn ?>" />
                </div>

                <div class="flex items-start flex-wrap gap-2">
                    <label class="flex items-center group has-[input:checked]:text-slate-900">
                        <input id="tmc" name="tmc" type="checkbox" required class="sr-only" />
                        <!-- Custom box -->
                        <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded outline-1 outline-slate-300 dark:outline-neutral-700
                              bg-white dark:bg-neutral-800
                              group-has-[input:checked]:bg-blue-600
                              group-has-[input:checked]:outline-blue-600
                              group-focus-within:outline-2
                              group-focus-within:outline-blue-600" aria-hidden="true">
                            <!-- Checkmark -->
                            <svg class="size-3 text-white opacity-0 group-has-[input:checked]:opacity-100" viewBox="0 0 12 10"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 5l3 3 7-7" />
                            </svg>
                        </span>
                        <span class="ml-3 text-sm text-slate-700 dark:text-slate-300">
                            I accept the
                        </span>
                    </label>

                    <a href="#"
                        class="ml-1 text-sm font-medium text-blue-700 dark:text-blue-500 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded">
                        Terms and Conditions
                    </a>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm">
                    Create an account</button>
            </div>
        </form>
    </div>
</main>