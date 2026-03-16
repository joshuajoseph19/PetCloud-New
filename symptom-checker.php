<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Symptom Checker - PetCloud</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 2.5rem;
            background: white;
            border-radius: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        h1 {
            font-family: 'Outfit';
            text-align: center;
            color: #111827;
        }

        .ai-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #3b82f6;
            font-weight: 700;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        .chat-area {
            border: 1px solid #f3f4f6;
            border-radius: 1.5rem;
            height: 350px;
            padding: 1.5rem;
            overflow-y: auto;
            margin: 2rem 0;
            background: #fcfcfc;
        }

        .msg {
            margin-bottom: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            max-width: 80%;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .msg-bot {
            background: #eff6ff;
            color: #1e40af;
            border-bottom-left-radius: 0;
        }

        .msg-user {
            background: #3b82f6;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }

        .input-group {
            display: flex;
            gap: 0.75rem;
        }

        input {
            flex: 1;
            padding: 1rem;
            border: 2px solid #f3f4f6;
            border-radius: 1rem;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: #3b82f6;
        }

        button {
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            border: none;
            background: #3b82f6;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="health-records.php"
            style="color: #3b82f6; text-decoration: none; font-weight: 600; font-size: 0.85rem;"><i
                class="fa-solid fa-arrow-left"></i> Back to Health</a>
        <div class="ai-badge" style="margin-top:2rem;"><i class="fa-solid fa-robot"></i> POWERED BY PETCLOUD AI</div>
        <h1>Symptom Checker</h1>
        <p style="text-align: center; color: #6b7280; font-size: 0.9rem;">What symptoms is your pet showing today?</p>

        <div class="chat-area" id="chat">
            <div class="msg msg-bot">Hello! I'm your AI Pet Health Assistant. Tell me what's going on with your furry
                friend, and I'll help you understand if it's an emergency or something you can monitor.</div>
        </div>

        <div class="input-group">
            <input type="text" id="userInput" placeholder="e.g. My dog has been coughing for 2 days...">
            <button onclick="sendMsg()">Analyze</button>
        </div>
    </div>

    <script>
        function sendMsg() {
            const input = document.getElementById('userInput');
            const chat = document.getElementById('chat');
            if (input.value.trim() === '') return;

            // User msg
            const uMsg = document.createElement('div');
            uMsg.className = 'msg msg-user';
            uMsg.textContent = input.value;
            chat.appendChild(uMsg);

            // Bot response (mock)
            setTimeout(() => {
                const bMsg = document.createElement('div');
                bMsg.className = 'msg msg-bot';
                bMsg.textContent = "I've analyzed the symptom: '" + input.value + "'. Based on common patterns, I recommend checking for a fever and ensuring your pet is hydrated. If the condition persists for more than 24 hours, please consult a vet.";
                chat.appendChild(bMsg);
                chat.scrollTop = chat.scrollHeight;
            }, 800);

            input.value = '';
            chat.scrollTop = chat.scrollHeight;
        }
    </script>
</body>

</html>