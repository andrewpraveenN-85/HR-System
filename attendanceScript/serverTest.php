<?php
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

class ServerTest {
    // Test results counter
    private $passedTests = 0;
    private $failedTests = 0;

    /**
     * Run all tests
     */
    public function runAllTests() {
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
    private function assertEquals($expected, $actual, $testName) {
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
    public function testFormatPunchDataValid() {
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
    public function testFormatPunchDataMultiple() {
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
    public function testFormatPunchDataEmpty() {
        $input = "";
        $result = formatPunchData($input);
        $this->assertEquals(null, $result, "formatPunchData with empty input");
    }

    /**
     * Test formatPunchData with invalid input
     */
    public function testFormatPunchDataInvalid() {
        $input = "InvalidData=Test\nAnotherInvalid=Entry";
        $result = formatPunchData($input);
        $this->assertEquals(null, $result, "formatPunchData with invalid input");
    }

    /**
     * Test sendToAPI function exists and is callable
     */
    public function testSendToAPIFunction() {
        $this->assertEquals(true, function_exists('sendToAPI'), "sendToAPI function exists");
        $this->assertEquals(true, is_callable('sendToAPI'), "sendToAPI function is callable");
    }

    /**
     * Integration test: process punch data and send to API
     */
    public function testProcessPunchData() {
        // kept for backward compatibility
        $this->testProcessPunchDataWith(null);
    }

    /**
     * Integration test: process punch data and send to API
     * Accept optional employeeData to run non-interactively.
     */
    public function testProcessPunchDataWith($employeeData = null) {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "=== Integration Test: API Data Submission ===\n";
        echo str_repeat("=", 50) . "\n";
        
        // Get employee data from user input if not provided
        if ($employeeData === null) {
            $employeeData = $this->getEmployeeIdsFromUser();
        }
        
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
            echo "1. Local (http://127.0.0.1:8000/api/attendance/store)\n";
            echo "2. Remote (https://hr.jaan.lk/api/attendance/store)\n";
            echo "3. Custom URL\n\n";
            
            $envChoice = readline("Choose (1/2/3): ");
            
            if ($envChoice == '1') {
                $apiUrl = "http://127.0.0.1:8000/api/attendance/store";
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
    private function analyzeAPIResponse($response) {
        $response = trim($response);
        
        // Check for common success indicators
        if (strpos($response, 'success') !== false || 
            strpos($response, '200') !== false || 
            strpos($response, '"status":"success"') !== false ||
            strpos($response, '"message":"success"') !== false) {
            
            echo "✅ PASS: API accepted the dummy data successfully!\n";
            echo "📊 Your Laravel controller is working correctly.\n";
            $this->passedTests++;
            
        } elseif (strpos($response, 'error') !== false || 
                  strpos($response, '400') !== false || 
                  strpos($response, '500') !== false ||
                  strpos($response, 'Curl error') !== false) {
            
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
    private function getEmployeeIdsFromUser() {
        $employeeData = [];
        
        echo "Attendance Test Data Setup:\n";
        echo "---------------------------\n";
    echo "1. Manual entry (specify employees and times)\n";
    echo "2. Quick test with default data\n";
    echo "3. Load test data from JSON file (attendance_tests.json)\n\n";
        
    echo "Choose option (1/2/3): ";
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

                // Collect IN time (supports combined IN-OUT like "08:30-17:30" or "YYYY-MM-DD 08:30-17:30")
                echo "\nIN Time options:\n";
                echo "1. Current time (" . date('Y-m-d H:i:s') . ")\n";
                echo "2. Morning (08:30- )\n";
                echo "3. Custom IN or combined IN-OUT (e.g., '08:30-17:30' or '2025-10-23 08:30-17:30')\n";
                echo "Choose IN time (1-3): ";
                $inChoice = trim(fgets($handle));

                $combinedProvided = false;
                $inTime = '';
                $outTime = '';

                switch ($inChoice) {
                    case '1':
                        $inTime = date('Y-m-d H:i:s');
                        break;
                    case '2':
                        $inTime = date('Y-m-d') . ' 08:30:00';
                        break;
                    case '3':
                    default:
                        echo "Enter custom IN time (YYYY-MM-DD HH:MM or YYYY-MM-DD HH:MM-HH:MM) or combined (HH:MM-HH:MM) : ";
                        $customIn = trim(fgets($handle));

                        // If user entered combined like 08:30-17:30 or 'YYYY-MM-DD 08:30-17:30'
                        if (!empty($customIn) && preg_match('/^(?:\d{4}-\d{2}-\d{2} )?\d{1,2}:\d{2}-\d{1,2}:\d{2}$/', $customIn)) {
                            $combinedProvided = true;
                            // If date present, split date and times
                            if (preg_match('/^(\d{4}-\d{2}-\d{2}) (\d{1,2}:\d{2})-(\d{1,2}:\d{2})$/', $customIn, $m)) {
                                $date = $m[1];
                                $inTime = $date . ' ' . $m[2] . ':00';
                                $outTime = $date . ' ' . $m[3] . ':00';
                            } else {
                                // No date, use today's date
                                if (preg_match('/^(\d{1,2}:\d{2})-(\d{1,2}:\d{2})$/', $customIn, $m2)) {
                                    $date = date('Y-m-d');
                                    $inTime = $date . ' ' . $m2[1] . ':00';
                                    $outTime = $date . ' ' . $m2[2] . ':00';
                                }
                            }
                        } else {
                            // Single datetime
                            if (empty($customIn) || strtotime($customIn) === false) {
                                if (!empty($customIn)) echo "Invalid format, using current time.\n";
                                $inTime = date('Y-m-d H:i:s');
                            } else {
                                // If time only provided (HH:MM), add today's date
                                if (preg_match('/^\d{1,2}:\d{2}$/', $customIn)) {
                                    $inTime = date('Y-m-d') . ' ' . $customIn . ':00';
                                } else {
                                    $inTime = $customIn;
                                }
                            }
                        }
                        break;
                }

                // Collect OUT time only if a combined time wasn't already provided
                if (!$combinedProvided) {
                    echo "\nOUT Time options:\n";
                    echo "1. Current time (" . date('Y-m-d H:i:s') . ")\n";
                    echo "2. Evening (17:30:00)\n";
                    echo "3. Custom OUT time\n";
                    echo "Choose OUT time (1-3): ";
                    $outChoice = trim(fgets($handle));

                    switch ($outChoice) {
                        case '1':
                            $outTime = date('Y-m-d H:i:s');
                            break;
                        case '2':
                            $outTime = date('Y-m-d') . ' 17:30:00';
                            break;
                        case '3':
                        default:
                            echo "Enter custom OUT time (YYYY-MM-DD HH:MM:SS) or press Enter for current time: ";
      
                            $customOut = trim(fgets($handle));
                            if (empty($customOut) || strtotime($customOut) === false) {
                                if (!empty($customOut)) echo "Invalid format, using current time.\n";
                                $outTime = date('Y-m-d H:i:s');
                            } else {
                                $outTime = $customOut;
                            }
                            break;
                    }
                }

                $employeeData[] = ['id' => $empId, 'in' => $inTime, 'out' => $outTime];
            }
        }

        // Option 3: load from JSON file
        if ($choice == '3') {
            $defaultPath = __DIR__ . DIRECTORY_SEPARATOR . 'attendance_tests.json';
            echo "\nEnter path to JSON file or press Enter to use: $defaultPath\n";
            echo "Path: ";
            $filePath = trim(fgets($handle));
            if (empty($filePath)) $filePath = $defaultPath;

            if (!file_exists($filePath)) {
                echo "File not found: $filePath\n";
            } else {
                $raw = file_get_contents($filePath);
                $json = json_decode($raw, true);
                $employees = [];

                if (is_array($json) && isset($json['item'])) {
                    foreach ($json['item'] as $item) {
                        // Only consider POST items with a body
                        $method = $item['request']['method'] ?? '';
                        if (strtoupper($method) !== 'POST') continue;
                        $bodyRaw = $item['request']['body']['raw'] ?? null;
                        if (empty($bodyRaw)) continue;

                        $body = json_decode($bodyRaw, true);
                        if ($body === null) {
                            // try to clean raw (sometimes escaped)
                            $bodyStr = trim($bodyRaw);
                            // attempt to remove leading/trailing quotes
                            if ((substr($bodyStr,0,1) === '"' && substr($bodyStr,-1) === '"') || (substr($bodyStr,0,1) === "'" && substr($bodyStr,-1) === "'")) {
                                $bodyStr = substr($bodyStr,1,-1);
                            }
                            $body = json_decode($bodyStr, true);
                        }

                        if (is_array($body)) {
                            // body can be array of entries or single entry
                            $entries = array_values($body);
                            foreach ($entries as $entry) {
                                if (!is_array($entry)) continue;
                                $id = $entry['EmpId'] ?? $entry['userId'] ?? $entry['userI'] ?? $entry['id'] ?? null;
                                if ($id === null) continue;
                                $date = $entry['date'] ?? date('Y-m-d');

                                // time can be in different fields
                                if (isset($entry['timeType']) && isset($entry['time'])) {
                                    $time = $entry['time'];
                                    // normalize time to full datetime
                                    if (preg_match('/^\d{2}:\d{2}$/', $time)) {
                                        $ts = $date . ' ' . $time . ':00';
                                    } else {
                                        $ts = $time;
                                    }

                                    if (!isset($employees[$id])) $employees[$id] = ['id' => $id, 'in' => '', 'out' => ''];
                                    $tt = strtolower($entry['timeType']);
                                    if (strpos($tt, 'morning') !== false || strpos($tt, 'in') !== false) {
                                        $employees[$id]['in'] = $ts;
                                    } elseif (strpos($tt, 'evening') !== false || strpos($tt, 'out') !== false) {
                                        $employees[$id]['out'] = $ts;
                                    } else {
                                        // fallback: set in if empty, else out
                                        if (empty($employees[$id]['in'])) $employees[$id]['in'] = $ts; else $employees[$id]['out'] = $ts;
                                    }
                                } elseif (isset($entry['inTime']) || isset($entry['outTime'])) {
                                    if (!isset($employees[$id])) $employees[$id] = ['id' => $id, 'in' => '', 'out' => ''];
                                    if (isset($entry['inTime'])) $employees[$id]['in'] = $entry['inTime'];
                                    if (isset($entry['outTime'])) $employees[$id]['out'] = $entry['outTime'];
                                } elseif (isset($entry['time'])) {
                                    // single time entries: append as in if empty else out
                                    $time = $entry['time'];
                                    if (preg_match('/^\d{2}:\d{2}$/', $time)) $ts = $date . ' ' . $time . ':00'; else $ts = $time;
                                    if (!isset($employees[$id])) $employees[$id] = ['id' => $id, 'in' => '', 'out' => ''];
                                    if (empty($employees[$id]['in'])) $employees[$id]['in'] = $ts; else $employees[$id]['out'] = $ts;
                                }
                            }
                        }
                    }
                }

                // convert to expected employeeData format
                if (!empty($employees)) {
                    $employeeData = array_values($employees);
                    echo "Loaded " . count($employeeData) . " employees from $filePath\n";
                    // show a compact preview
                    foreach ($employeeData as $ed) {
                        $in = $ed['in'] ?? '';
                        $out = $ed['out'] ?? '';
                        echo "  - {$ed['id']} IN: {$in} OUT: {$out}\n";
                    }
                } else {
                    echo "No suitable attendance entries found in $filePath\n";
                }
            }
        }
        
        // If no employee data was provided or option 2 was chosen, use default test data
        if (empty($employeeData) || $choice == '2') {
            $now = date('Y-m-d H:i:s');
            $morning = date('Y-m-d') . ' 08:30:00';
            $evening = date('Y-m-d') . ' 17:30:00';

            $employeeData = [
                ['id' => 'TEST001', 'in' => $morning, 'out' => $evening],
                ['id' => 'TEST002', 'in' => $morning, 'out' => $now],
                ['id' => 'TEST003', 'in' => $now, 'out' => $evening]
            ];
            echo "\nUsing default test data:\n";
        } else {
            echo "\nYour attendance test data:\n";
        }
        
        // Display the test data
        echo "-------------------------\n";
        foreach ($employeeData as $data) {
            $in = isset($data['in']) ? $data['in'] : (isset($data['time']) ? $data['time'] : '');
            $out = isset($data['out']) ? $data['out'] : '';
            echo "Employee: {$data['id']} | IN: {$in}";
            if (!empty($out)) echo " | OUT: {$out}";
            echo "\n";
        }
        echo "\n";
        
        fclose($handle);
        return $employeeData;
    }
    
    /**
     * Generate dummy punch data for testing
     */
    private function generateDummyPunchData($employeeData = []) {
        $punchEntries = [];
        foreach ($employeeData as $data) {
            $empId = $data['id'];
            // If the entry has separate in/out times, create two punch entries
            if (isset($data['in'])) {
                $punchEntries[] = "EmpId=$empId,AttTime={$data['in']}";
            }
            if (isset($data['out'])) {
                $punchEntries[] = "EmpId=$empId,AttTime={$data['out']}";
            }
            // Backwards compatibility: single 'time' field
            if (!isset($data['in']) && !isset($data['out']) && isset($data['time'])) {
                $punchEntries[] = "EmpId=$empId,AttTime={$data['time']}";
            }
        }

        return implode("\n", $punchEntries);
    }

    /**
     * Load employee in/out data from a Postman-style collection JSON file.
     * Returns array of ['id'=>..., 'in'=>..., 'out'=>...] or empty array.
     */
    public function loadEmployeeDataFromJsonFile($filePath) {
        if (!file_exists($filePath)) return [];
        $raw = file_get_contents($filePath);
        $json = json_decode($raw, true);
        $employees = [];

        if (is_array($json) && isset($json['item'])) {
            foreach ($json['item'] as $item) {
                $method = $item['request']['method'] ?? '';
                if (strtoupper($method) !== 'POST') continue;
                $bodyRaw = $item['request']['body']['raw'] ?? null;
                if (empty($bodyRaw)) continue;

                $body = json_decode($bodyRaw, true);
                if ($body === null) {
                    $bodyStr = trim($bodyRaw);
                    if ((substr($bodyStr,0,1) === '"' && substr($bodyStr,-1) === '"') || (substr($bodyStr,0,1) === "'" && substr($bodyStr,-1) === "'")) {
                        $bodyStr = substr($bodyStr,1,-1);
                    }
                    $body = json_decode($bodyStr, true);
                }

                if (is_array($body)) {
                    $entries = array_values($body);
                    foreach ($entries as $entry) {
                        if (!is_array($entry)) continue;
                        $id = $entry['EmpId'] ?? $entry['userId'] ?? $entry['userI'] ?? $entry['id'] ?? null;
                        if ($id === null) continue;
                        $date = $entry['date'] ?? date('Y-m-d');

                        if (isset($entry['timeType']) && isset($entry['time'])) {
                            $time = $entry['time'];
                            if (preg_match('/^\d{2}:\d{2}$/', $time)) $ts = $date . ' ' . $time . ':00'; else $ts = $time;
                            if (!isset($employees[$id])) $employees[$id] = ['id' => $id, 'in' => '', 'out' => ''];
                            $tt = strtolower($entry['timeType']);
                            if (strpos($tt, 'morning') !== false || strpos($tt, 'in') !== false) {
                                $employees[$id]['in'] = $ts;
                            } elseif (strpos($tt, 'evening') !== false || strpos($tt, 'out') !== false) {
                                $employees[$id]['out'] = $ts;
                            } else {
                                if (empty($employees[$id]['in'])) $employees[$id]['in'] = $ts; else $employees[$id]['out'] = $ts;
                            }
                        } elseif (isset($entry['inTime']) || isset($entry['outTime'])) {
                            if (!isset($employees[$id])) $employees[$id] = ['id' => $id, 'in' => '', 'out' => ''];
                            if (isset($entry['inTime'])) $employees[$id]['in'] = $entry['inTime'];
                            if (isset($entry['outTime'])) $employees[$id]['out'] = $entry['outTime'];
                        } elseif (isset($entry['time'])) {
                            $time = $entry['time'];
                            if (preg_match('/^\d{2}:\d{2}$/', $time)) $ts = $date . ' ' . $time . ':00'; else $ts = $time;
                            if (!isset($employees[$id])) $employees[$id] = ['id' => $id, 'in' => '', 'out' => ''];
                            if (empty($employees[$id]['in'])) $employees[$id]['in'] = $ts; else $employees[$id]['out'] = $ts;
                        }
                    }
                }
            }
        }

        return array_values($employees);
    }

    /**
     * Run only the API test without unit tests
     */
    public function runAPITestOnly() {
        echo "=== Quick API Test ===\n\n";
        $this->testProcessPunchData();
        echo "\nQuick test completed.\n";
    }
    
    /**
     * Create a simple attendance entry for quick testing
     */
    private function createQuickTestData() {
        return [
            ['id' => 'QUICK001', 'in' => date('Y-m-d') . ' 09:00:00', 'out' => date('Y-m-d') . ' 17:00:00']
        ];
    }
}

// Check if this script is being run directly
if (php_sapi_name() === 'cli') {
    $opts = getopt('', ['file:', 'stdin']);
    $tester = new ServerTest();

    // If --stdin is provided, read JSON from STDIN and parse
    if (isset($opts['stdin'])) {
        $stdin = stream_get_contents(STDIN);
        $data = json_decode($stdin, true);
        if (is_array($data)) {
            $employees = $tester->loadEmployeeDataFromJsonFile('');
            // if loadEmployeeDataFromJsonFile can't handle empty, try parse directly
            if (empty($employees)) {
                // expect same format as attendance_tests.json (top-level 'item')
                if (isset($data['item'])) {
                    // write to temp file and reuse loader
                    $tmp = tempnam(sys_get_temp_dir(), 'at_') . '.json';
                    file_put_contents($tmp, json_encode($data));
                    $employees = $tester->loadEmployeeDataFromJsonFile($tmp);
                    unlink($tmp);
                }
            }

            if (!empty($employees)) {
                $tester->testProcessPunchDataWith($employees);
                exit(0);
            }
        }
    }

    // If --file is provided, load that JSON and run non-interactively
    if (isset($opts['file'])) {
        $filePath = $opts['file'];
        $employees = $tester->loadEmployeeDataFromJsonFile($filePath);
        if (!empty($employees)) {
            $tester->testProcessPunchDataWith($employees);
            exit(0);
        } else {
            echo "No employee data found in $filePath\n";
            exit(1);
        }
    }

    // Fallback to interactive mode
    echo "Attendance Server Test Script\n";
    echo "============================\n\n";
    echo "Choose test mode:\n";
    echo "1. Run all tests (unit + integration)\n";
    echo "2. Run API test only (quick test)\n\n";
    echo "Enter choice (1/2): ";

    $handle = fopen("php://stdin", "r");
    $choice = trim(fgets($handle));
    fclose($handle);

    if ($choice == '2') {
        $tester->runAPITestOnly();
    } else {
        $tester->runAllTests();
    }

    echo "\nTest script completed. Press any key to exit...\n";
    fread(STDIN, 1);
}