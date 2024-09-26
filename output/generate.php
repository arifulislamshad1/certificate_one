<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload file
require '../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Load the image template (replace with your template path)
    $template = imagecreatefromjpeg('../templates/certificate_template.jpg');

    // Check if image loading was successful
    if (!$template) {
        die("Error: Unable to load the image template.");
    }

    // Set font file paths
    $font_gabriola = '../fonts/Gabriola.ttf'; // Path to Gabriola font
    $font_qtcoronation = '../fonts/QTCoronation.ttf'; // Path to QTCoronation font (for the name)

    // Check if fonts exist
    if (!file_exists($font_gabriola) || !file_exists($font_qtcoronation)) {
        die("Error: One or more font files not found.");
    }

    // Set text colors (RGB 89, 90, 90 and black)
    $custom_color = imagecolorallocate($template, 89, 90, 90);
    $black_color = imagecolorallocate($template, 0, 0, 0);
    $issu_color = imagecolorallocate($template, 48, 48, 49);
    $reg_color = imagecolorallocate($template, 35, 31, 32);

    // Set font sizes
    $font_size_name = 130;  // Font size for the name
    $font_size_normal = 40; // Font size for normal text
    $font_size_bold = 43;   // Font size for bold text

    // Get form data
    $name = trim($_POST['name']);
    $father_name = trim($_POST['father_name']);
    $mother_name = trim($_POST['mother_name']);
    $reg_no = trim($_POST['reg_no']);
    $issue_date = date("jS F, Y", strtotime($_POST['issue_date']));
    $gender = $_POST['gender'];
    $email = $_POST['email']; // Fetch email input from form

    // Ensure there is only one space between words
    $father_name = preg_replace('/\s+/', ' ', $father_name); // Replaces multiple spaces with a single space
    $mother_name = preg_replace('/\s+/', ' ', $mother_name); // Replaces multiple spaces with a single space

    // Gender-specific text (using full father_name and mother_name)
    if ($gender == 'Female') {
        $parent_text = "daughter of  $father_name &  $mother_name"; 
    } else {
        $parent_text = "son of  $father_name &  $mother_name";
    }

    // Course and Organization Info
    $course_info = "$parent_text for successfully completing the ";
    $course_name = "Organic Mehendi Making Course"; // Bold
    $organization_info = "conducted by ";
    $organization = "Alhadiya"; // Bold

    // Calculate image width and height
    $image_width = 2430; // Width in pixels
    $image_height = 1890; // Height in pixels

    // Center the Name Text using QTCoronation Font
    $text_box_name = imagettfbbox($font_size_name, 0, $font_qtcoronation, $name);
    $text_width_name = abs($text_box_name[4] - $text_box_name[0]);
    $name_x_position = ($image_width / 2) - ($text_width_name / 2); // Centered X position
    $name_y_position = 970; // Adjust Y position as needed
    imagettftext($template, $font_size_name, 0, $name_x_position, $name_y_position, $black_color, $font_qtcoronation, $name);

    // Add Registration Number
    $reg_x_position = 457;
    $reg_y_position = 645;
    imagettftext($template, $font_size_normal, 0, $reg_x_position, $reg_y_position, $reg_color, $font_gabriola, $reg_no);

    // First Line: $parent_text and "Organic Mehendi Making Course"
    $first_line_combined = $course_info . $course_name;
    $text_box_first_line_combined = imagettfbbox($font_size_normal, 0, $font_gabriola, $first_line_combined);
    $text_width_first_line_combined = abs($text_box_first_line_combined[4] - $text_box_first_line_combined[0]);

    $first_line_x_position_combined = ($image_width / 2) - ($text_width_first_line_combined / 2) - 20;
    $first_line_y_position = 1150;

    imagettftext($template, $font_size_normal, 0, $first_line_x_position_combined, $first_line_y_position, $custom_color, $font_gabriola, $course_info);
    imagettftext($template, $font_size_bold, 0, $first_line_x_position_combined + abs(imagettfbbox($font_size_normal, 0, $font_gabriola, $course_info)[2]), $first_line_y_position, $custom_color, $font_gabriola, $course_name);

    // Second Line: "conducted by" + "Alhadiya"
    $second_line_combined = $organization_info . $organization;
    $text_box_second_line_combined = imagettfbbox($font_size_normal, 0, $font_gabriola, $second_line_combined);
    $text_width_second_line_combined = abs($text_box_second_line_combined[4] - $text_box_second_line_combined[0]);
    $second_line_x_position_combined = ($image_width / 2) - ($text_width_second_line_combined / 2); 
    $second_line_y_position = $first_line_y_position + 60;

    imagettftext($template, $font_size_normal, 0, $second_line_x_position_combined, $second_line_y_position, $custom_color, $font_gabriola, $organization_info);

    // Bold "Alhadiya"
    $bold_offsets = [-1, 1];
    foreach ($bold_offsets as $offset) {
        imagettftext($template, $font_size_bold, 0, $second_line_x_position_combined + abs(imagettfbbox($font_size_normal, 0, $font_gabriola, $organization_info)[2]), $second_line_y_position + $offset, $custom_color, $font_gabriola, $organization);
    }

    // Add Issue Date
    $date_x_position = 1281;
    $date_y_position = 1621;
    imagettftext($template, $font_size_normal, 0, $date_x_position, $date_y_position, $issu_color, $font_gabriola, $issue_date);

    // Save the certificate as PNG
    $output_filename_png = '../output/certificate_' . strtolower($name) . '.png';
    imagepng($template, $output_filename_png);

    // Free up memory
    imagedestroy($template);

    // Convert PNG to PDF using FPDF
    $output_filename_pdf = '../output/certificate_' . strtolower($name) . '.pdf';
    $image_width_pt = $image_width * 0.75;  // Convert to points
    $image_height_pt = $image_height * 0.75; // Convert to points

    $pdf = new \FPDF('L', 'pt', [$image_width_pt, $image_height_pt]); // Set page size to landscape
    $pdf->AddPage();
    $pdf->Image($output_filename_png, 0, 0, $image_width_pt, $image_height_pt); // No scaling, use full size
    $pdf->Output($output_filename_pdf, 'F');

    // Send Email if 'send' button is clicked
    if (isset($_POST['send'])) {
        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'shadvoia71@gmail.com'; // Your email
            $mail->Password = '';    // Your app password (from Google)
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Email Settings
            $mail->setFrom('shadvoia71@gmail.com', 'Alhadiya Organic Mehendi Course ');
            $mail->addAddress($email); // Send to recipient's email

            // Attachments
            $mail->addAttachment($output_filename_png, 'Certificate.png');
            $mail->addAttachment($output_filename_pdf, 'Certificate.pdf');

    // Email Content
$mail->isHTML(true);
$mail->Subject = 'Alhadiya Organic Mehendi Course certificate';
$mail->Body    = 'Thank You for Completing the Alhadiya Organic Mehendi Course <br><br>
                  Please find your certificate attached in both PNG and PDF formats.<br><br>
                  If you have any questions or need further assistance, please feel free to reach out.<br><br>
                  Best regards,<br>
                  Md. Ariful Islam Shad<br>
                  Website: <a href="http://alhadiya.com.bd">alhadiya.com.bd</a><br>
                  Contact No: 01737146996<br>
                  Page Link: <a href="https://web.facebook.com/alhadiyaofficial">facebook.com/alhadiyaofficial</a>';


            $mail->send();
            echo "Email sent successfully!";
        } catch (Exception $e) {
            echo "Failed to send email: {$mail->ErrorInfo}";
        }
    }

    // Output options (Preview or Download)
    if (isset($_POST['preview'])) {
        header('Content-Type: image/png');
        imagepng($template);
    } elseif (isset($_POST['download'])) {
        echo "Certificate generated successfully! <br>";
        echo "<a href='$output_filename_png' download>Download PNG Certificate</a> | <a href='$output_filename_pdf' download>Download PDF Certificate</a>";
    }
}
?>
