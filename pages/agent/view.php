<?php
$llms = $config['llms'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Agent System</title>
  <link rel="stylesheet" href="pages/agent/style.css">
</head>
<body>
  <div class="container">
    <h1>AI Agent System</h1>
    
    <div class="goal-form">
      <div class="form-group">
        <label for="llm">Select LLM:</label>
        <select id="llm">
          <?php foreach($llms as $key => $llm): ?>
            <option value="<?= $key ?>"><?= ucfirst($key) ?> (<?= $llm['model'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="form-group">
        <label for="goal">Enter Goal:</label>
        <textarea id="goal" rows="4"></textarea>
      </div>
      
      <button id="submit-goal">Start Agent</button>
    </div>
    
    <div class="output-container">
      <h2>Agent Output</h2>
      <pre id="output"></pre>
    </div>
  </div>
  
  <script src="pages/agent/controller.js"></script>
</body>
</html>
