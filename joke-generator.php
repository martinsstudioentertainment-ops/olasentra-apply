<?php

declare(strict_types=1);

/**
 * Random Joke Generator
 * Fetches jokes from the JokeAPI external API
 */

$joke = null;
$error = null;
$jokeType = $_GET['type'] ?? 'general';

// Validate joke type
$allowedTypes = ['general', 'programming', 'knock-knock'];
if (!in_array($jokeType, $allowedTypes, true)) {
    $jokeType = 'general';
}

// Map to JokeAPI categories
$categoryMap = [
    'general' => 'General',
    'programming' => 'Programming',
    'knock-knock' => 'Knock-knock'
];

$category = $categoryMap[$jokeType];

// Fetch joke from external API
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['fetch'])) {
    
    try {
        
        // JokeAPI endpoint
        $apiUrl = "https://v2.jokeapi.dev/joke/{$category}?format=json";
        
        // Use cURL or file_get_contents to fetch
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to fetch joke from API. HTTP Code: ' . $httpCode);
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API');
        }
        
        if (isset($data['error']) && $data['error']) {
            throw new Exception('API Error: ' . ($data['message'] ?? 'Unknown error'));
        }
        
        // Format the joke
        if ($data['type'] === 'single') {
            $joke = [
                'text' => $data['joke'],
                'category' => $data['category'],
                'type' => 'Single'
            ];
        } else {
            $joke = [
                'setup' => $data['setup'],
                'delivery' => $data['delivery'],
                'category' => $data['category'],
                'type' => 'Two-part'
            ];
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Random Joke Generator</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 20px;
}

.container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    padding: 40px;
    max-width: 600px;
    width: 100%;
}

h1 {
    text-align: center;
    color: #333;
    margin-bottom: 10px;
    font-size: 32px;
}

.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 30px;
    font-size: 14px;
}

.controls {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.type-selector {
    flex: 1;
    min-width: 150px;
}

.type-selector label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
    font-size: 14px;
}

.type-selector select {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.type-selector select:hover {
    border-color: #667eea;
}

.type-selector select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-group {
    display: flex;
    gap: 10px;
}

.btn {
    flex: 1;
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-fetch {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-fetch:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-fetch:active {
    transform: translateY(0);
}

.btn-reset {
    background: #f0f0f0;
    color: #333;
}

.btn-reset:hover {
    background: #e0e0e0;
}

.joke-display {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 20px;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.joke-content {
    text-align: center;
    width: 100%;
}

.joke-text {
    color: #333;
    font-size: 18px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.joke-setup {
    color: #333;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 15px;
    font-weight: 600;
}

.joke-delivery {
    color: #667eea;
    font-size: 16px;
    line-height: 1.6;
    margin-top: 15px;
    font-style: italic;
}

.joke-type {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 10px;
}

.joke-category {
    display: inline-block;
    background: #764ba2;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 8px;
    margin-top: 10px;
}

.empty-state {
    color: #999;
    font-size: 16px;
}

.error {
    background: #fff3cd;
    border: 2px solid #ffc107;
    color: #856404;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.success {
    background: #d4edda;
    border: 2px solid #28a745;
    color: #155724;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.info {
    background: #e7f3ff;
    border: 2px solid #b3d9ff;
    color: #004085;
    padding: 15px;
    border-radius: 10px;
    margin-top: 20px;
    font-size: 14px;
    text-align: center;
}

.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 600px) {
    .container {
        padding: 20px;
    }
    
    h1 {
        font-size: 24px;
    }
    
    .controls {
        flex-direction: column;
    }
    
    .type-selector {
        min-width: 100%;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .joke-text {
        font-size: 16px;
    }
}

</style>

</head>

<body>

<div class="container">

    <h1>😂 Joke Generator</h1>
    <p class="subtitle">Get random jokes from the JokeAPI</p>

    <?php if (!empty($error)): ?>
    <div class="error">
        <strong>⚠️ Error:</strong> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="controls">
        
        <div class="type-selector">
            <label for="jokeType">Joke Type:</label>
            <form method="GET" id="typeForm">
                <select id="jokeType" name="type" onchange="document.getElementById('typeForm').submit()">
                    <option value="general" <?= $jokeType === 'general' ? 'selected' : '' ?>>General</option>
                    <option value="programming" <?= $jokeType === 'programming' ? 'selected' : '' ?>>Programming</option>
                    <option value="knock-knock" <?= $jokeType === 'knock-knock' ? 'selected' : '' ?>>Knock-Knock</option>
                </select>
            </form>
        </div>

        <div class="btn-group">
            <form method="POST" style="flex: 1;">
                <button type="submit" class="btn btn-fetch">
                    ✨ Get Joke
                </button>
            </form>
            
            <a href="?type=<?= htmlspecialchars($jokeType) ?>" class="btn btn-reset">
                🔄 Reset
            </a>
        </div>

    </div>

    <div class="joke-display">
        <?php if ($joke): ?>
            <div class="joke-content">
                <?php if ($joke['type'] === 'Single'): ?>
                    <div class="joke-text">
                        <?= htmlspecialchars($joke['text']) ?>
                    </div>
                <?php else: ?>
                    <div class="joke-setup">
                        <?= htmlspecialchars($joke['setup']) ?>
                    </div>
                    <div class="joke-delivery">
                        <?= htmlspecialchars($joke['delivery']) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <span class="joke-type"><?= htmlspecialchars($joke['type']) ?></span>
                    <span class="joke-category"><?= htmlspecialchars($joke['category']) ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                👉 Click "Get Joke" to generate a random joke!
            </div>
        <?php endif; ?>
    </div>

    <div class="info">
        <strong>💡 Tip:</strong> Select a joke type and click "Get Joke" to fetch a random joke from the JokeAPI. Try all three types!
    </div>

</div>

</body>

</html>
