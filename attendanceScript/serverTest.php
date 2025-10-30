<?php
// Force script to run in Sri Lanka time
date_default_timezone_set('Asia/Colombo');
// Copy the functions from server.php without running the server

/**
 * Formats the raw punch data into JSON structure required by Laravel API.
 */
function formatPunchData($punchData)
{
    $entries = explode("\n", $punchData);
    $formattedEntries = [];

    foreach ($entries as $entry) {
        $entry = trim($entry);
        if (!empty($entry)) {
            // Assume format: "EmpId=12345,AttTime=2025-02-15 08:30:00"
            parse_str(str_replace(",", "&", $entry), $data);

            if (isset($data['EmpId']) && isset($data['AttTime'])) {
                $formattedEntries[] = [
                    'EmpId' => $data['EmpId'],
                    'AttTime' => $data['AttTime']
                ];
            }
        }
    }

    return !empty($formattedEntries) ? json_encode($formattedEntries) : null;
}

/**
 * Send formatted punch data to the external API.
 */
function sendToAPI($url, $jsonData)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $response = 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);

    return $response;
}

class ServerTest
{
    // Test results counter
    private $passedTests = 0;
    private $failedTests = 0;

    /**
     * Run all tests
     */
    public function runAllTests()
    {
        echo "=== Attendance Server Test Suite ===\n";
        echo "Testing server.php functions and API integration\n\n";

        // Run unit tests
        $this->testFormatPunchDataValid();
        $this->testFormatPunchDataMultiple();
        $this->testFormatPunchDataEmpty();
        $this->testFormatPunchDataInvalid();
        $this->testSendToAPIFunction();

        // Run integration test
        $this->testProcessPunchData();

        // Print test summary
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Test Summary: {$this->passedTests} passed, {$this->failedTests} failed\n";
        if ($this->failedTests === 0) {
            echo "🎉 All tests passed!\n";
        }
    }

    /**
     * Assert that two values are equal
     */
    private function assertEquals($expected, $actual, $testName)
    {
        if ($expected === $actual) {
            echo "✅ PASS: $testName\n";
            $this->passedTests++;
        } else {
            echo "❌ FAIL: $testName\n";
            echo "  Expected: " . var_export($expected, true) . "\n";
            echo "  Actual: " . var_export($actual, true) . "\n";
            $this->failedTests++;
        }
    }

    /**
     * Test formatPunchData with valid single entry
     */
    public function testFormatPunchDataValid()
    {
        $input = "EmpId=12345,AttTime=2025-10-01 08:30:00";
        $expected = json_encode([
            ['EmpId' => '12345', 'AttTime' => '2025-10-01 08:30:00']
        ]);

        $result = formatPunchData($input);
        $this->assertEquals($expected, $result, "formatPunchData with valid single entry");
    }

    /**
     * Test formatPunchData with multiple entries
     */
    public function testFormatPunchDataMultiple()
    {
        $input = "EmpId=12345,AttTime=2025-10-01 08:30:00\nEmpId=67890,AttTime=2025-10-01 09:15:00";
        $expected = json_encode([
            ['EmpId' => '12345', 'AttTime' => '2025-10-01 08:30:00'],
            ['EmpId' => '67890', 'AttTime' => '2025-10-01 09:15:00']
        ]);

        $result = formatPunchData($input);
        $this->assertEquals($expected, $result, "formatPunchData with multiple entries");
    }

    /**
     * Test formatPunchData with empty input
     */
    public function testFormatPunchDataEmpty()
    {
        $input = "";
        $result = formatPunchData($input);
        $this->assertEquals(null, $result, "formatPunchData with empty input");
    }

    /**
     * Test formatPunchData with invalid input
     */
    public function testFormatPunchDataInvalid()
    {
        $input = "InvalidData=Test\nAnotherInvalid=Entry";
        $result = formatPunchData($input);
        $this->assertEquals(null, $result, "formatPunchData with invalid input");
    }

    /**
     * Test sendToAPI function exists and is callable
     */
    public function testSendToAPIFunction()
    {
        $this->assertEquals(true, function_exists('sendToAPI'), "sendToAPI function exists");
        $this->assertEquals(true, is_callable('sendToAPI'), "sendToAPI function is callable");
    }

    /**
     * Integration test: process punch data and send to API
     */
    public function testProcessPunchData()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "=== Integration Test: API Data Submission ===\n";
        echo str_repeat("=", 50) . "\n";

        // Get employee data from user input
        $employeeData = $this->getEmployeeIdsFromUser();

        // Generate dummy test data for specified employees
        $dummyData = $this->generateDummyPunchData($employeeData);

        echo "\nRaw punch data generated:\n";
        echo "------------------------\n";
        echo $dummyData . "\n\n";

        // Format the data as it would come from the device
        $formattedData = formatPunchData($dummyData);

        if (!empty($formattedData)) {
            echo "✅ Successfully formatted punch data\n";
            echo "Formatted JSON data:\n";
            echo "-------------------\n";
            echo $formattedData . "\n\n";

            // Pretty print the JSON for better readability
            $decodedData = json_decode($formattedData, true);
            echo "Parsed data preview:\n";
            echo "-------------------\n";
            foreach ($decodedData as $index => $entry) {
                echo "Entry " . ($index + 1) . ": Employee ID = {$entry['EmpId']}, Time = {$entry['AttTime']}\n";
            }
            echo "\n";

            // Test sending to the API
            echo "Select API endpoint:\n";
            echo "1. Local (http://127.0.0.1:8001/api/attendance/store)\n";
            echo "2. Remote (https://hr.jaan.lk/api/attendance/store)\n";
            echo "3. Custom URL\n\n";

            $envChoice = readline("Choose (1/2/3): ");

            if ($envChoice == '1') {
                $apiUrl = "http://127.0.0.1:8001/api/attendance/store";
            } elseif ($envChoice == '2') {
                $apiUrl = "https://hr.jaan.lk/api/attendance/store";
            } else {
                $apiUrl = readline("Enter custom URL: ");
            }

            echo "\n⚠️ Ready to send test data to: $apiUrl\n";
            echo "This will make an actual API call to your Laravel application.\n\n";

            // Confirm with the user before proceeding
            echo "Do you want to proceed with sending this data? (y/n): ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);

            if (trim(strtolower($line)) == 'y') {
                echo "\n🚀 Sending data to API...\n";
                $response = sendToAPI($apiUrl, $formattedData);

                echo "\nAPI Response:\n";
                echo "-------------\n";
                echo $response . "\n\n";

                // Analyze the response
                $this->analyzeAPIResponse($response);
            } else {
                echo "\n⏭️ API call skipped by user.\n";
                echo "✅ Test data generation and formatting completed successfully.\n";
            }
            fclose($handle);
        } else {
            echo "❌ FAIL: Could not format punch data\n";
            $this->failedTests++;
        }
    }

    /**
     * Analyze API response and provide feedback
     */
    private function analyzeAPIResponse($response)
    {
        $response = trim($response);

        // Check for common success indicators
        if (
            strpos($response, 'success') !== false ||
            strpos($response, '200') !== false ||
            strpos($response, '"status":"success"') !== false ||
            strpos($response, '"message":"success"') !== false
        ) {

            echo "✅ PASS: API accepted the dummy data successfully!\n";
            echo "📊 Your Laravel controller is working correctly.\n";
            $this->passedTests++;
        } elseif (
            strpos($response, 'error') !== false ||
            strpos($response, '400') !== false ||
            strpos($response, '500') !== false ||
            strpos($response, 'Curl error') !== false
        ) {

            echo "❌ FAIL: API returned an error\n";
            echo "🔍 Check your Laravel controller and database connection.\n";
            $this->failedTests++;
        } else {
            echo "⚠️ UNKNOWN: Unexpected API response format\n";
            echo "🔍 Please check the response manually to determine if it succeeded.\n";
            echo "💡 Tip: Look for status codes or success/error messages in the response.\n";
        }
    }

    /**
     * Prompt user for employee IDs and their attendance times
     */
    private function getEmployeeIdsFromUser()
    {
        $employeeData = [];

        echo "Attendance Test Data Setup:\n";
        echo "---------------------------\n";
        echo "1. Manual entry (specify employees and times)\n";
        echo "2. Quick test with default data\n\n";

        echo "Choose option (1/2): ";
        $handle = fopen("php://stdin", "r");
        $choice = trim(fgets($handle));

        if ($choice == '1') {
            echo "\nHow many employees do you want to test? ";
            $empCount = (int)trim(fgets($handle));

            for ($i = 1; $i <= $empCount; $i++) {
                echo "\n--- Employee $i ---\n";
                echo "Employee ID: ";
                $empId = trim(fgets($handle));

                if (empty($empId)) {
                    echo "Empty employee ID, skipping...\n";
                    continue;
                }

                echo "Attendance Time:\n";
                $nowPreview = (new DateTime())->format('Y-m-d H:i:s');
                echo "1. Current time ({$nowPreview})\n";
                echo "2. Morning (08:30:00)\n";
                echo "3. Evening (17:30:00)\n";
                echo "4. Custom time\n";
                echo "Choose (1-4): ";
                $timeChoice = trim(fgets($handle));

                switch ($timeChoice) {
                    case '1':
                        $dt = new DateTime();
                        $attTime = $dt->format('Y-m-d H:i:s');
                        break;

                    case '2':
                        $attTime = date('Y-m-d 08:30:00');
                        // $attTime = date('2025-10-03 10:30:00');

                    case '3':
                        $attTime = date('Y-m-d 18:30:00');
                        // $attTime = date('2025-10-03 10:30:00');s  
                        break;

                    case '4':
                        echo "Enter custom time (YYYY-MM-DD HH:MM:SS) or press Enter for current time: ";
                        $customTime = trim(fgets($handle));
                        if (empty($customTime)) {
                            $attTime = (new DateTime())->format('Y-m-d H:i:s');
                        } else {
                            // Validate time format
                            if (strtotime($customTime) === false) {
                                echo "Invalid time format, using current time.\n";
                                $attTime = (new DateTime())->format('Y-m-d H:i:s');
                            } else {
                                $attTime = $customTime;
                            }
                        }
                        break;

                    default:
                        // Default to current time
                        $attTime = (new DateTime())->format('Y-m-d H:i:s');
                        break;
                }

                $employeeData[] = ['id' => $empId, 'time' => $attTime];
            }
        }

        // If no employee data was provided or option 2 was chosen, use default test data
        if (empty($employeeData) || $choice == '2') {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $morning = (new DateTime('today 08:30:00'))->format('Y-m-d H:i:s');
            $evening = (new DateTime('today 17:30:00'))->format('Y-m-d H:i:s');

            $employeeData = [
                ['id' => 'TEST001', 'time' => $morning],
                ['id' => 'TEST002', 'time' => $now],
                ['id' => 'TEST003', 'time' => $evening]
            ];
            echo "\nUsing default test data:\n";
        } else {
            echo "\nYour attendance test data:\n";
        }

        // Display the test data
        echo "-------------------------\n";
        foreach ($employeeData as $data) {
            echo "Employee: {$data['id']} | Time: {$data['time']}\n";
        }
        echo "\n";

        fclose($handle);
        return $employeeData;
    }

    /**
     * Generate dummy punch data for testing
     */
    private function generateDummyPunchData($employeeData = [])
    {
        $punchEntries = [];

        foreach ($employeeData as $data) {
            $empId = $data['id'];
            $timestamp = $data['time'];
            $punchEntries[] = "EmpId=$empId,AttTime=$timestamp";
        }

        return implode("\n", $punchEntries);
    }

    /**
     * Run only the API test without unit tests
     */
    public function runAPITestOnly()
    {
        echo "=== Quick API Test ===\n\n";
        $this->testProcessPunchData();
        echo "\nQuick test completed.\n";
    }

    /**
     * Create a simple attendance entry for quick testing
     */
    private function createQuickTestData()
    {
        return [
            ['id' => 'QUICK001', 'time' => (new DateTime())->format('Y-m-d H:i:s')]
        ];
    }
}

// Check if this script is being run directly
if (php_sapi_name() === 'cli') {
    echo "Attendance Server Test Script\n";
    echo "============================\n\n";
    echo "Choose test mode:\n";
    echo "1. Run all tests (unit + integration)\n";
    echo "2. Run API test only (quick test)\n\n";
    echo "Enter choice (1/2): ";

    $handle = fopen("php://stdin", "r");
    $choice = trim(fgets($handle));
    fclose($handle);

    $tester = new ServerTest();

    if ($choice == '2') {
        $tester->runAPITestOnly();
    } else {
        $tester->runAllTests();
    }

    echo "\nTest script completed. Press any key to exit...\n";
    fread(STDIN, 1);
}
