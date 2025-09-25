<?php
header('Content-Type: application/json');

// Discord webhook
$webhookURL = 'https://discord.com/api/webhooks/...';

// IP de l'utilisateur
$ip = $_SERVER['REMOTE_ADDR'];

// Fichier de stockage des timestamps
$file = 'help_requests.json';
$data = [];
if(file_exists($file)) $data = json_decode(file_get_contents($file), true);

// Vérifie si l'IP a déjà soumis il y a moins de 24h
if(isset($data[$ip]) && time() - $data[$ip] < 24*60*60){
    echo json_encode(['success'=>false,'error'=>'You can only send one request every 24 hours.']);
    exit;
}

// Récupération du formulaire
$discord = htmlspecialchars($_POST['discord'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$message = htmlspecialchars($_POST['message'] ?? '');

if(!$discord || !$email || !$message){
    echo json_encode(['success'=>false,'error'=>'Please fill all fields.']);
    exit;
}

// Crée l’embed
$embed = [
    "title" => "🆘 New Help Request",
    "color" => hexdec("ff0058"),
    "fields" => [
        ["name"=>"💬 Discord", "value"=>$discord, "inline"=>true],
        ["name"=>"📧 Email", "value"=>$email, "inline"=>true],
        ["name"=>"📝 Message", "value"=>$message]
    ],
    "footer" => ["text"=>"GlobeTrotter VTC Support"]
];

$payload = json_encode(["embeds"=>[$embed]]);

// Envoi vers Discord
$ch = curl_init($webhookURL);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$result = curl_exec($ch);
curl_close($ch);

// Sauvegarde le timestamp pour l’IP
$data[$ip] = time();
file_put_contents($file, json_encode($data));

echo json_encode(['success'=>true]);
