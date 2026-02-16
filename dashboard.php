<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Get other user's profile (admin sees user, user sees admin)
$isAdmin = $_SESSION['role'] === 'Administrator';
$otherUser = $isAdmin ? [
    'user_id' => 2,
    'username' => 'user',
    'name' => 'Regular User',
    'role' => 'User',
    'email' => 'user@example.com',
    'status' => 'online',
    'avatar' => '👤'
] : [
    'user_id' => 1,
    'username' => 'admin',
    'name' => 'Admin User',
    'role' => 'Administrator',
    'email' => 'admin@example.com',
    'status' => 'online',
    'avatar' => '👨‍💼'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Video Calling</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            font-size: 24px;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: white;
            color: #667eea;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Alert Box */
        .alert-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-box.show {
            display: block;
        }

        .alert-box.error {
            background: #fee2e2;
            border-left-color: #ef4444;
        }

        .alert-box.success {
            background: #d1fae5;
            border-left-color: #10b981;
        }

        .alert-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .alert-message {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        .alert-steps {
            margin-top: 10px;
            padding-left: 20px;
        }

        .alert-steps li {
            margin: 5px 0;
            font-size: 13px;
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
        }

        /* Profile Section */
        .profile-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .profile-avatar {
            font-size: 80px;
            margin-bottom: 15px;
        }

        .profile-name {
            font-size: 22px;
            color: #333;
            margin-bottom: 5px;
        }

        .profile-role {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .badge-admin {
            background: #667eea;
            color: white;
        }

        .badge-user {
            background: #48bb78;
            color: white;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            color: #48bb78;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #48bb78;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .profile-info {
            margin-top: 25px;
        }

        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .call-actions {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-call {
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-video-call {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-video-call:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-audio-call {
            background: #48bb78;
            color: white;
        }

        .btn-audio-call:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.4);
        }

        /* Video Section */
        .video-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .video-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
        }

        .video-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .video-header h3 {
            color: #333;
            font-size: 18px;
        }

        .call-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-idle {
            background: #e2e8f0;
            color: #64748b;
        }

        .status-calling {
            background: #fef3c7;
            color: #92400e;
        }

        .status-connected {
            background: #d1fae5;
            color: #065f46;
        }

        .video-display {
            background: #000;
            border-radius: 8px;
            position: relative;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        #remoteVideo, #localVideo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #remoteVideo {
            display: none;
        }

        #localVideo {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 200px;
            height: 150px;
            border-radius: 8px;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: none;
        }

        .video-placeholder {
            text-align: center;
            color: #666;
        }

        .video-placeholder-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .video-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .control-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: none;
        }

        .control-btn.active {
            display: block;
        }

        .btn-mute {
            background: #3b82f6;
            color: white;
        }

        .btn-mute:hover {
            background: #2563eb;
        }

        .btn-video-toggle {
            background: #8b5cf6;
            color: white;
        }

        .btn-video-toggle:hover {
            background: #7c3aed;
        }

        .btn-end-call {
            background: #ef4444;
            color: white;
        }

        .btn-end-call:hover {
            background: #dc2626;
        }

        /* Session Info */
        .session-info {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .session-info h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .session-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .session-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .session-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .session-value {
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        @media (max-width: 1024px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }

            #localVideo {
                width: 120px;
                height: 90px;
                bottom: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>📹 Video Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
            <a href="?logout=1" class="btn-logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Alert Box -->
        <div class="alert-box" id="alertBox">
            <div class="alert-title" id="alertTitle"></div>
            <div class="alert-message" id="alertMessage"></div>
        </div>

        <div class="dashboard-layout">
            <!-- Profile Section -->
            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-avatar"><?php echo $otherUser['avatar']; ?></div>
                    <h2 class="profile-name"><?php echo htmlspecialchars($otherUser['name']); ?></h2>
                    <span class="profile-role <?php echo $otherUser['role'] === 'Administrator' ? 'badge-admin' : 'badge-user'; ?>">
                        <?php echo htmlspecialchars($otherUser['role']); ?>
                    </span>
                    <div class="status-indicator">
                        <span class="status-dot"></span>
                        <span><?php echo ucfirst($otherUser['status']); ?></span>
                    </div>
                </div>

                <div class="profile-info">
                    <div class="info-row">
                        <div class="info-label">User ID</div>
                        <div class="info-value">#<?php echo $otherUser['user_id']; ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Username</div>
                        <div class="info-value">@<?php echo htmlspecialchars($otherUser['username']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($otherUser['email']); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Availability</div>
                        <div class="info-value" style="color: #48bb78;">Available for calls</div>
                    </div>
                </div>

                <div class="call-actions">
                    <button class="btn-call btn-video-call" onclick="startVideoCall()">
                        📹 Start Video Call
                    </button>
                    <button class="btn-call btn-audio-call" onclick="startAudioCall()">
                        📞 Start Audio Call
                    </button>
                </div>
            </div>

            <!-- Video Section -->
            <div class="video-section">
                <div class="video-container">
                    <div class="video-header">
                        <h3>Video Call</h3>
                        <span class="call-status status-idle" id="callStatus">Idle</span>
                    </div>

                    <div class="video-display" id="videoDisplay">
                        <video id="remoteVideo" autoplay playsinline></video>
                        <video id="localVideo" autoplay playsinline muted></video>
                        <div class="video-placeholder" id="videoPlaceholder">
                            <div class="video-placeholder-icon">🎥</div>
                            <p>Start a video call to begin</p>
                        </div>
                    </div>

                    <div class="video-controls">
                        <button class="control-btn btn-mute" id="muteBtn" onclick="toggleMute()">🎤</button>
                        <button class="control-btn btn-video-toggle" id="videoBtn" onclick="toggleVideo()">📹</button>
                        <button class="control-btn btn-end-call active" id="endCallBtn" onclick="endCall()">📞</button>
                    </div>
                </div>

                <div class="session-info">
                    <h3>Your Session Information</h3>
                    <div class="session-grid">
                        <div class="session-card">
                            <div class="session-label">Your User ID</div>
                            <div class="session-value">#<?php echo $_SESSION['user_id']; ?></div>
                        </div>
                        <div class="session-card">
                            <div class="session-label">Your Role</div>
                            <div class="session-value"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
                        </div>
                        <div class="session-card">
                            <div class="session-label">Login Time</div>
                            <div class="session-value"><?php echo htmlspecialchars($_SESSION['login_time']); ?></div>
                        </div>
                        <div class="session-card">
                            <div class="session-label">Session ID</div>
                            <div class="session-value"><?php echo substr(session_id(), 0, 8); ?>...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let localStream = null;
        let remoteStream = null;
        let peerConnection = null;
        let isMuted = false;
        let isVideoOff = false;

        const configuration = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };

        // Check if page is loaded over HTTPS (or localhost)
        function checkSecureContext() {
            const isSecure = window.isSecureContext;
            const isLocalhost = location.hostname === 'localhost' || location.hostname === '127.0.0.1';

            if (!isSecure && !isLocalhost) {
                showAlert('error', 
                    '⚠️ HTTPS Required', 
                    'Camera and microphone access requires HTTPS. Please access this page via HTTPS or use localhost for testing.<br><br>' +
                    '<strong>Current URL:</strong> ' + location.protocol + '//' + location.host
                );
                return false;
            }
            return true;
        }

        async function startVideoCall() {
            if (!checkSecureContext()) {
                return;
            }

            try {
                updateCallStatus('calling', 'Requesting permissions...');

                showAlert('success', 
                    '📱 Permission Required', 
                    'Please allow camera and microphone access when prompted by your browser.'
                );

                // Request permissions with explicit constraints
                const constraints = {
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user'
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                };

                localStream = await navigator.mediaDevices.getUserMedia(constraints);

                showAlert('success', 
                    '✅ Connected', 
                    'Camera and microphone access granted successfully!'
                );

                setTimeout(() => hideAlert(), 3000);

                document.getElementById('localVideo').srcObject = localStream;
                document.getElementById('localVideo').style.display = 'block';
                document.getElementById('videoPlaceholder').style.display = 'none';

                // Create peer connection
                peerConnection = new RTCPeerConnection(configuration);

                // Add local stream tracks to peer connection
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });

                // Handle remote stream
                peerConnection.ontrack = (event) => {
                    if (!remoteStream) {
                        remoteStream = new MediaStream();
                        document.getElementById('remoteVideo').srcObject = remoteStream;
                    }
                    remoteStream.addTrack(event.track);
                    document.getElementById('remoteVideo').style.display = 'block';
                };

                // Simulate connection
                setTimeout(() => {
                    updateCallStatus('connected', 'Connected');
                    showControls();
                    simulateRemoteConnection();
                }, 1500);

            } catch (error) {
                console.error('Error starting video call:', error);
                handleMediaError(error);
                endCall();
            }
        }

        async function startAudioCall() {
            if (!checkSecureContext()) {
                return;
            }

            try {
                updateCallStatus('calling', 'Requesting permissions...');

                showAlert('success', 
                    '📱 Permission Required', 
                    'Please allow microphone access when prompted by your browser.'
                );

                const constraints = {
                    video: false,
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                };

                localStream = await navigator.mediaDevices.getUserMedia(constraints);

                showAlert('success', 
                    '✅ Connected', 
                    'Microphone access granted successfully!'
                );

                setTimeout(() => hideAlert(), 3000);

                document.getElementById('videoPlaceholder').innerHTML = 
                    '<div class="video-placeholder-icon">📞</div><p>Audio call in progress...</p>';

                setTimeout(() => {
                    updateCallStatus('connected', 'Connected');
                    showControls();
                    document.getElementById('videoBtn').style.display = 'none';
                }, 1500);

            } catch (error) {
                console.error('Error starting audio call:', error);
                handleMediaError(error);
                endCall();
            }
        }

        function handleMediaError(error) {
            let title = '⚠️ Permission Denied';
            let message = '';

            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                message = '<strong>On iPhone/Safari:</strong><ol class="alert-steps">' +
                    '<li>Go to <strong>Settings</strong> → <strong>Safari</strong> → <strong>Camera</strong></li>' +
                    '<li>Select "Ask" or "Allow"</li>' +
                    '<li>Go to <strong>Settings</strong> → <strong>Safari</strong> → <strong>Microphone</strong></li>' +
                    '<li>Select "Ask" or "Allow"</li>' +
                    '<li>Reload this page and try again</li>' +
                    '</ol><br>' +
                    '<strong>Alternative:</strong> Try using Chrome or Firefox browser on your iPhone.';
            } else if (error.name === 'NotFoundError') {
                message = 'No camera or microphone found on your device.';
            } else if (error.name === 'NotReadableError') {
                message = 'Camera or microphone is already in use by another application. Please close other apps and try again.';
            } else if (error.name === 'NotSupportedError') {
                message = 'Camera/microphone access is not supported. Make sure you are using HTTPS or localhost.';
            } else {
                message = 'Error: ' + error.message + '<br><br>Please check your browser settings and permissions.';
            }

            showAlert('error', title, message);
        }

        function simulateRemoteConnection() {
            const simulatedRemoteStream = localStream.clone();
            document.getElementById('remoteVideo').srcObject = simulatedRemoteStream;
            document.getElementById('remoteVideo').style.display = 'block';
        }

        function toggleMute() {
            if (localStream) {
                isMuted = !isMuted;
                localStream.getAudioTracks().forEach(track => {
                    track.enabled = !isMuted;
                });
                document.getElementById('muteBtn').innerHTML = isMuted ? '🔇' : '🎤';
                document.getElementById('muteBtn').style.background = isMuted ? '#ef4444' : '#3b82f6';
            }
        }

        function toggleVideo() {
            if (localStream) {
                isVideoOff = !isVideoOff;
                localStream.getVideoTracks().forEach(track => {
                    track.enabled = !isVideoOff;
                });
                document.getElementById('videoBtn').innerHTML = isVideoOff ? '❌' : '📹';
                document.getElementById('videoBtn').style.background = isVideoOff ? '#ef4444' : '#8b5cf6';
            }
        }

        function endCall() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }

            if (peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }

            document.getElementById('localVideo').style.display = 'none';
            document.getElementById('remoteVideo').style.display = 'none';
            document.getElementById('videoPlaceholder').style.display = 'block';
            document.getElementById('videoPlaceholder').innerHTML = 
                '<div class="video-placeholder-icon">🎥</div><p>Start a video call to begin</p>';

            hideControls();
            updateCallStatus('idle', 'Idle');
            isMuted = false;
            isVideoOff = false;
        }

        function updateCallStatus(statusClass, statusText) {
            const statusElement = document.getElementById('callStatus');
            statusElement.className = 'call-status status-' + statusClass;
            statusElement.textContent = statusText;
        }

        function showControls() {
            document.getElementById('muteBtn').classList.add('active');
            document.getElementById('videoBtn').classList.add('active');
        }

        function hideControls() {
            document.getElementById('muteBtn').classList.remove('active');
            document.getElementById('videoBtn').classList.remove('active');
            document.getElementById('muteBtn').innerHTML = '🎤';
            document.getElementById('videoBtn').innerHTML = '📹';
            document.getElementById('muteBtn').style.background = '#3b82f6';
            document.getElementById('videoBtn').style.background = '#8b5cf6';
        }

        function showAlert(type, title, message) {
            const alertBox = document.getElementById('alertBox');
            const alertTitle = document.getElementById('alertTitle');
            const alertMessage = document.getElementById('alertMessage');

            alertBox.className = 'alert-box show ' + type;
            alertTitle.textContent = title;
            alertMessage.innerHTML = message;
        }

        function hideAlert() {
            const alertBox = document.getElementById('alertBox');
            alertBox.classList.remove('show');
        }

        // Check browser compatibility on load
        window.addEventListener('load', () => {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showAlert('error', 
                    '❌ Browser Not Supported', 
                    'Your browser does not support camera/microphone access. Please use a modern browser like Chrome, Firefox, or Safari.'
                );
            }
        });
    </script>
</body>
</html>