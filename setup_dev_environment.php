<?php
/**
 * CW Academy Development Environment Setup Script
 */

class CWADevSetup {
    
    private $dbHost;
    private $dbPort;
    private $dbHostPort;
    private $dbName;
    private $dbUser;
    private $dbPassword;
    private $pdo;
    private $isWindows;
        
    public function __construct() {
        $this->isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
    
    public function run() {
        echo "===========================================\n";
        echo "CW Academy Development Environment Setup\n";
        echo "===========================================\n\n";
        
        $this->runStartupScript();
        $this->loadEnvFile();
//        $this->waitForMySQL();
        $this->connectToDatabase();
        $callsign = $this->getUserCallsign();
        $this->configureAdministrator($callsign);
        
        echo "\n===========================================\n";
        echo "Setup Complete!\n";
        echo "===========================================\n";
    }
    
	private function runStartupScript() {
		echo "Step 1: Running startup script...\n";
		
		if ($this->isWindows) {
			$script = 'startup.ps1';
			if (!file_exists($script)) {
				$this->error("startup.ps1 not found in current directory");
			}
			echo "Detected Windows - running startup.ps1\n";
			$command = "powershell -ExecutionPolicy Bypass -File " . $script;
		} else {
			$script = 'startup.sh';
			if (!file_exists($script)) {
				$this->error("startup.sh not found in current directory");
			}
			echo "Detected Unix/Linux/Mac - running startup.sh\n";
			chmod($script, 0755);
			$command = "bash " . $script;
		}
		
		echo "Executing: " . $command . "\n";
		echo "------- Startup Script Output -------\n";
		
		// Use passthru instead of exec to stream output directly
		$returnCode = 0;
		passthru($command . " 2>&1", $returnCode);
		
		echo "------- End Startup Script Output -------\n";
		
		if ($returnCode !== 0) {
			$this->error("Startup script failed with return code: " . $returnCode);
		}
		
		echo "Startup script completed successfully.\n\n";
	}
    
	private function loadEnvFile() {
		echo "Step 2: Loading environment variables...\n";
		
		$envFile = '.env';
		
		if (!file_exists($envFile)) {
			$this->error(".env file not found in current directory");
		}
		
		$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$this->dbPort = '3306'; // Default port
		
		foreach ($lines as $line) {
			if (strpos(trim($line), '#') === 0) {
				continue;
			}
			
			if (strpos($line, '=') !== false) {
				$parts = explode('=', $line, 2);
				$key = trim($parts[0]);
				$value = trim($parts[1]);
				$value = trim($value, '"\'');
				
				$keyLower = strtolower($key);
				if ($keyLower === 'db_host') {
					// Check if port is included in host (e.g., db:3306)
					if (strpos($value, ':') !== false) {
						$hostParts = explode(':', $value, 2);
						$this->dbHost = $hostParts[0];
						$this->dbPort = $hostParts[1];
					} else {
						$this->dbHost = $value;
					}
				} elseif ($keyLower === 'db_port') {
					$this->dbPort = $value;
				} elseif ($keyLower === 'db_name') {
					$this->dbName = $value;
				} elseif ($keyLower === 'db_user') {
					$this->dbUser = $value;
				} elseif ($keyLower === 'db_password') {
					$this->dbPassword = $value;
				} elseif ($keyLower === 'db_host_port') {
					$this->dbHostPort = $value;
				}
			}
		}
		
		// Override db_host if it's a Docker internal hostname
		if ($this->dbHost === 'db' || $this->dbHost === 'mysql' || $this->dbHost === 'database') {
			echo "  Note: Converting Docker hostname '" . $this->dbHost . "' to '127.0.0.1' for host access\n";
			$this->dbHost = '127.0.0.1';
			
			// Use host port if specified (for Docker port mapping)
			if (!empty($this->dbHostPort)) {
				$this->dbPort = $this->dbHostPort;
			}
		}		

		if (empty($this->dbHost)) {
			$this->error("db_host not found in .env file");
		}
		if (empty($this->dbName)) {
			$this->error("db_name not found in .env file");
		}
		if (empty($this->dbUser)) {
			$this->error("db_user not found in .env file");
		}
		if (empty($this->dbPassword)) {
			$this->error("db_password not found in .env file");
		}
		
		echo "  Database Host: " . $this->dbHost . "\n";
		echo "  Database Port: " . $this->dbPort . "\n";
		echo "  Database Name: " . $this->dbName . "\n";
		echo "  Database User: " . $this->dbUser . "\n";
		echo "Environment variables loaded successfully.\n\n";
	}    
 
	private function waitForMySQL() {
		echo "Step 3: Waiting for MySQL to be ready...\n";
		
		$maxAttempts = 60;
		$attempt = 0;
		$waitSeconds = 3;
		
		while ($attempt < $maxAttempts) {
			$attempt++;
			echo "  Attempt " . $attempt . "/" . $maxAttempts . "...\n";
			
			try {
				$dsn = "mysql:host=" . $this->dbHost . ";port=" . $this->dbPort . ";dbname=" . $this->dbName;
				$pdo = new PDO($dsn, $this->dbUser, $this->dbPassword);
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo = null;
				echo "MySQL is ready!\n\n";
				return;
			} catch (PDOException $e) {
				echo "  MySQL not ready yet: " . $e->getMessage() . "\n";
				sleep($waitSeconds);
			}
		}
		
		$this->error("MySQL did not become ready within " . ($maxAttempts * $waitSeconds) . " seconds");
	}
    
	private function connectToDatabase() {
		echo "Step 4: Connecting to database...\n";
		
		try {
			$dsn = "mysql:host=" . $this->dbHost . ";port=" . $this->dbPort . ";dbname=" . $this->dbName . ";charset=utf8mb4";
			$this->pdo = new PDO($dsn, $this->dbUser, $this->dbPassword);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			echo "Connected to database successfully.\n\n";
		} catch (PDOException $e) {
			$this->error("Database connection failed: " . $e->getMessage());
		}
	}
    
    private function getUserCallsign() {
//        echo "Step 5: Get user callsign...\n";
//        echo "Enter your CW Academy login (amateur radio callsign): ";
//        
//        $handle = fopen("php://stdin", "r");
//        $callsign = trim(fgets($handle));
//        fclose($handle);
//        
//        if (empty($callsign)) {
//            $this->error("Callsign cannot be empty");
//        }
//        
//        $callsign = strtoupper($callsign);
//        
//        echo "Using callsign: " . $callsign . "\n\n";
//        
//        return $callsign;
		  return 'K7OJL';
    }
    
	private function configureAdministrator($callsign) {
		echo "Step 6: Configuring " . $callsign . " as administrator...\n";
		
		echo "  Looking up user in wpw1_users...\n";
		
		$stmt = $this->pdo->prepare("SELECT ID FROM wpw1_users WHERE user_login = :callsign");
		$stmt->execute(array('callsign' => $callsign));
		$user = $stmt->fetch();
		
		if (!$user) {
			$this->error("User '" . $callsign . "' not found in wpw1_users table");
		}
		
		$userId = $user['ID'];
		echo "  Found user ID: " . $userId . "\n";
		
		echo "  Updating WordPress capabilities...\n";
		
		$adminCapabilities = 'a:1:{s:13:"administrator";b:1;}';
		
		$stmt = $this->pdo->prepare(
			"SELECT umeta_id FROM wpw1_usermeta WHERE user_id = :user_id AND meta_key = 'wpw1_capabilities'"
		);
		$stmt->execute(array('user_id' => $userId));
		$meta = $stmt->fetch();
		
		if ($meta) {
			$stmt = $this->pdo->prepare(
				"UPDATE wpw1_usermeta SET meta_value = :meta_value WHERE user_id = :user_id AND meta_key = 'wpw1_capabilities'"
			);
			$stmt->execute(array(
				'meta_value' => $adminCapabilities,
				'user_id' => $userId
			));
			echo "  WordPress capabilities updated.\n";
		} else {
			$stmt = $this->pdo->prepare(
				"INSERT INTO wpw1_usermeta (user_id, meta_key, meta_value) VALUES (:user_id, 'wpw1_capabilities', :meta_value)"
			);
			$stmt->execute(array(
				'user_id' => $userId,
				'meta_value' => $adminCapabilities
			));
			echo "  WordPress capabilities inserted.\n";
		}
		
		echo "  Updating CW Academy user_master...\n";
		
		$stmt = $this->pdo->prepare(
			"SELECT user_id FROM wpw1_cwa_user_master WHERE user_call_sign = :callsign"
		);
		$stmt->execute(array('callsign' => $callsign));
		$cwaMaster = $stmt->fetch();
		
		if (!$cwaMaster) {
			echo "  WARNING: User '" . $callsign . "' not found in wpw1_cwa_user_master table.\n";
			echo "  Skipping CW Academy admin flag update.\n";
		} else {
			$stmt = $this->pdo->prepare(
				"UPDATE wpw1_cwa_user_master SET user_is_admin = 'Y' WHERE user_call_sign = :callsign"
			);
			$stmt->execute(array('callsign' => $callsign));
			echo "  CW Academy admin flag set to 'Y'.\n";
		}
		
		echo "  Disabling 'JAVASCRIPT: Refresh Requests' snippet...\n";
		
		$stmt = $this->pdo->prepare(
			"SELECT id FROM wpw1_snippets WHERE name = :name"
		);
		$stmt->execute(array('name' => 'JAVASCRIPT: Refresh Requests'));
		$snippet = $stmt->fetch();
		
		if (!$snippet) {
			echo "  WARNING: Snippet 'JAVASCRIPT: Refresh Requests' not found in wpw1_snippets table.\n";
			echo "  Skipping snippet deactivation.\n";
		} else {
			$stmt = $this->pdo->prepare(
				"UPDATE wpw1_snippets SET active = 0 WHERE name = :name"
			);
			$stmt->execute(array('name' => 'JAVASCRIPT: Refresh Requests'));
			echo "  Snippet 'JAVASCRIPT: Refresh Requests' deactivated.\n";
		}
		
		echo "\nUser '" . $callsign . "' has been configured as an administrator.\n";
	}
    
    private function error($message) {
        echo "\nERROR: " . $message . "\n";
        exit(1);
    }
}

$setup = new CWADevSetup();
$setup->run();
