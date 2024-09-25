<?php
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

    // Ensure there is only one space between words
    $father_name = preg_replace('/\s+/', ' ', $father_name); // Replaces multiple spaces with a single space
    $mother_name = preg_replace('/\s+/', ' ', $mother_name); // Replaces multiple spaces with a single space

    // Gender-specific text (using full father_name and mother_name)
    if ($gender == 'Female') {
        $parent_text = "daughter of  $father_name &  $mother_name";  // Space after "daughter of" and between "mother_name"
    } else {
        $parent_text = "son of  $father_name &  $mother_name";  // Space after "son of" and between "mother_name"
    }

    // Course and Organization Info
    $course_info = "$parent_text for successfully completing the ";
    $course_name = "Organic Mehendi Making Course"; // Bold
    $organization_info = "conducted by ";
    $organization = "Alhadiya"; // Bold

    // Calculate image width
    $image_width = imagesx($template);

    // Center the Name Text using QTCoronation Font
    $text_box_name = imagettfbbox($font_size_name, 0, $font_qtcoronation, $name);
    $text_width_name = abs($text_box_name[4] - $text_box_name[0]);
    $name_x_position = ($image_width / 2) - ($text_width_name / 2); // Centered X position
    $name_y_position = 970; // Adjust Y position as needed
    imagettftext($template, $font_size_name, 0, $name_x_position, $name_y_position, $black_color, $font_qtcoronation, $name);

    // Add Registration Number (also in RGB(89, 90, 90))
    $reg_x_position = 457;
    $reg_y_position = 645;
    imagettftext($template, $font_size_normal, 0, $reg_x_position, $reg_y_position, $reg_color, $font_gabriola, $reg_no);

    // First Line: $parent_text and "Organic Mehendi Making Course"
    // Combined normal and bold text
    $first_line_combined = $course_info . $course_name;
    $text_box_first_line_combined = imagettfbbox($font_size_normal, 0, $font_gabriola, $first_line_combined);
    $text_width_first_line_combined = abs($text_box_first_line_combined[4] - $text_box_first_line_combined[0]);

    // Adjust X-position to move 20px left
    $first_line_x_position_combined = ($image_width / 2) - ($text_width_first_line_combined / 2) - 20;
    $first_line_y_position = 1150; // Adjust Y position as needed

    // Render the full first line (including both normal and bold parts)
    imagettftext($template, $font_size_normal, 0, $first_line_x_position_combined, $first_line_y_position, $custom_color, $font_gabriola, $course_info);
    imagettftext($template, $font_size_bold, 0, $first_line_x_position_combined + abs(imagettfbbox($font_size_normal, 0, $font_gabriola, $course_info)[2]), $first_line_y_position, $custom_color, $font_gabriola, $course_name);

    // Second Line: "conducted by" + "Alhadiya"
    $second_line_combined = $organization_info . $organization;
    $text_box_second_line_combined = imagettfbbox($font_size_normal, 0, $font_gabriola, $second_line_combined);
    $text_width_second_line_combined = abs($text_box_second_line_combined[4] - $text_box_second_line_combined[0]);
    $second_line_x_position_combined = ($image_width / 2) - ($text_width_second_line_combined / 2); // Centering the second line based on image width
    $second_line_y_position = $first_line_y_position + 60; // Adjust Y position for second line

    // Render the second part (normal: "conducted by")
    imagettftext($template, $font_size_normal, 0, $second_line_x_position_combined, $second_line_y_position, $custom_color, $font_gabriola, $organization_info);

    // Bold part: "Alhadiya"
    $bold_offsets = [-1, 1]; // For bold effect
    foreach ($bold_offsets as $offset) {
        imagettftext($template, $font_size_bold, 0, $second_line_x_position_combined + abs(imagettfbbox($font_size_normal, 0, $font_gabriola, $organization_info)[2]), $second_line_y_position + $offset, $custom_color, $font_gabriola, $organization);
    }


    // Add Issue Date (also in RGB(89, 90, 90))
    $date_x_position = 1281;
    $date_y_position = 1621;
    imagettftext($template, $font_size_normal, 0, $date_x_position, $date_y_position, $issu_color, $font_gabriola, $issue_date);

    // Output options based on user's selection (Preview or Download)
    if (isset($_POST['preview'])) {
        header('Content-Type: image/png');
        imagepng($template);
    } elseif (isset($_POST['download'])) {
        $output_filename = '../output/certificate_' . strtolower($name) . '.png';
        imagepng($template, $output_filename);
        echo "Certificate generated successfully! <br>";
        echo "<a href='$output_filename' download>Download Certificate</a>";
    }

    // Free up memory
    imagedestroy($template);
}
?>
