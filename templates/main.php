<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telemedicina Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar-fixed {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 16rem;
            z-index: 10;
            transition: transform 0.3s;
        }
        @media (max-width: 1023px) {
            .sidebar-fixed {
                transform: translateX(-100%);
            }
            .sidebar-fixed.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
        }
        .main-content {
            margin-left: 16rem;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }
        @media (max-width: 1023px) {
            .main-content {
                margin-left: 0;
            }
        }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            z-index: 9;
        }
        .sidebar-backdrop.open {
            display: block;
        }
        .notification-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .message-bubble {
            max-width: 70%;
            word-wrap: break-word;
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .urgent-notification {
            border-left: 4px solid #ef4444;
            background: #fef2f2;
        }
        .hover-scale:hover {
            transform: scale(1.02);
            transition: transform 0.2s;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div id="app">
        <!-- Login Screen -->
        <div id="loginScreen" class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800">Telemedicina Platform</h1>
                    <p class="text-gray-600 mt-2">Válassza ki a felhasználói szerepkört</p>
                </div>

                <form action="/" method="post" class="space-y-3">
                    <input type="hidden" name="role" value="admin">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 hover-scale">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Adminisztrátor
                    </button>
                </form>
                <form action="/" method="post" class="space-y-3">
                    <input type="hidden" name="role" value="caregiver">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 hover-scale">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Gondozó
                    </button>
                </form>
                <form action="/" method="post" class="space-y-3">
                    <input type="hidden" name="role" value="pharmacist">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 hover-scale">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                        </svg>
                        Gyógyszerész
                    </button>
                </form>
                <form action="/" method="post" class="space-y-3">
                    <input type="hidden" name="role" value="sponsor">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 hover-scale">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Támogató
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Application -->
        <div id="mainApp" style="display: none;">
            <!-- Sidebar -->
            <div class="sidebar-fixed bg-white shadow-lg">
                <div class="p-6 border-b">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 id="userName" class="font-semibold text-gray-800"></h3>
                            <p id="userRole" class="text-sm text-gray-600 capitalize"></p>
                        </div>
                    </div>
                </div>

                <nav class="p-4">
                    <div class="space-y-2">
                        <button onclick="showView('dashboard')" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-gray-600 hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Dashboard
                        </button>

                        <button id="therapyBtn" onclick="showView('therapy')" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-gray-600 hover:bg-gray-50" style="display: none;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Terápiák
                        </button>

                        <button id="medicationsBtn" onclick="showView('medications')" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-gray-600 hover:bg-gray-50" style="display: none;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                            </svg>
                            Gyógyszerek
                        </button>

                        <button onclick="showView('chat')" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-gray-600 hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Chat
                        </button>

                        <button onclick="showView('notifications')" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-gray-600 hover:bg-gray-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11.613 15.931c0-1.32.027-2.171.027-3.343C11.64 9.182 9.321 6.863 6.32 6.863a5.697 5.697 0 00-4.868 2.8c-1.506 2.59-.866 5.921 1.42 7.991.897.813 2.074 1.277 3.355 1.277"></path>
                            </svg>
                            Értesítések
                            <span id="notificationBadge" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full notification-badge">3</span>
                        </button>

                        <button id="patientsBtn" onclick="showView('patients')" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors text-gray-600 hover:bg-gray-50" style="display: none;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Betegek
                        </button>
                    </div>
                </nav>

                <div class="absolute bottom-4 left-4 right-4">
                    <a href="/?action=logout" class="w-full flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Kijelentkezés
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="main-content p-8">
                <!-- Dashboard View -->
                <div id="dashboardView" class="view">
                    <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard</h1>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm border hover-scale">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Aktív betegek</p>
                                    <p class="text-2xl font-bold text-gray-800"><?php echo $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(); ?></p>
                                </div>
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border hover-scale">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Mai terápiák</p>
                                    <p class="text-2xl font-bold text-gray-800"><?php echo $pdo->query("SELECT COUNT(*) FROM therapies")->fetchColumn(); ?></p>
                                </div>
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border hover-scale">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Gyógyszerek</p>
                                    <p class="text-2xl font-bold text-gray-800"><?php echo $pdo->query("SELECT COUNT(*) FROM medications")->fetchColumn(); ?></p>
                                </div>
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border hover-scale">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Értesítések</p>
                                    <p class="text-2xl font-bold text-gray-800"><?php echo $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn(); ?></p>
                                </div>
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11.613 15.931c0-1.32.027-2.171.027-3.343C11.64 9.182 9.321 6.863 6.32 6.863a5.697 5.697 0 00-4.868 2.8c-1.506 2.59-.866 5.921 1.42 7.991.897.813 2.074 1.277 3.355 1.277"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm border">
                            <h3 class="text-lg font-semibold mb-4">Legutóbbi terápiák</h3>
                            <div class="space-y-3">
                                <?php
                                $stmt = $pdo->query("SELECT * FROM therapies LIMIT 3");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($row['patient']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['type']); ?></p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium status-<?php echo htmlspecialchars($row['status']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                                    </span>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-sm border">
                            <h3 class="text-lg font-semibold mb-4">Sürgős értesítések</h3>
                            <div class="space-y-3">
                                <?php
                                $stmt = $pdo->query("SELECT * FROM notifications ORDER BY urgent DESC LIMIT 2");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                <div class="<?php echo $row['urgent'] ? 'urgent-notification' : 'p-3 bg-blue-50 border-l-4 border-blue-500'; ?> p-3 rounded-lg">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 <?php echo $row['urgent'] ? 'text-red-600' : 'text-blue-600'; ?> mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium <?php echo $row['urgent'] ? 'text-red-800' : 'text-blue-800'; ?>"><?php echo htmlspecialchars($row['text']); ?></p>
                                            <p class="text-xs <?php echo $row['urgent'] ? 'text-red-600' : 'text-blue-600'; ?> mt-1"><?php echo htmlspecialchars($row['time']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat View -->
                <div id="chatView" class="view" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-8">Chat</h1>

                    <div class="bg-white rounded-xl shadow-sm border h-96 flex flex-col">
                        <div class="flex-1 p-6 overflow-y-auto" id="chatMessages">
                            <div class="space-y-4">
                                <?php
                                $stmt = $pdo->query("SELECT * FROM chat_messages ORDER BY id");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                <div class="flex justify-start">
                                    <div class="message-bubble bg-gray-100 text-gray-800 px-4 py-2 rounded-lg">
                                        <p class="text-sm font-medium mb-1"><?php echo htmlspecialchars($row['sender']); ?></p>
                                        <p><?php echo htmlspecialchars($row['text']); ?></p>
                                        <p class="text-xs mt-1 opacity-75"><?php echo htmlspecialchars($row['time']); ?></p>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div class="border-t p-4">
                            <form id="chatForm" action="/" method="post" class="flex gap-3">
                                <input type="hidden" name="action" value="chat">
                                <input type="text" name="text" id="messageInput" placeholder="Írjon üzenetet..." class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Therapy View -->
                <div id="therapyView" class="view" style="display: none;">
                    <div class="flex justify-between items-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Terápiák</h1>
                        <button onclick="document.getElementById('addTherapyModal').style.display = 'block'" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover-scale">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Új terápia
                        </button>
                    </div>
                    <div class="space-y-4">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM therapies");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($row['patient']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['type']); ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium status-<?php echo htmlspecialchars($row['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                            </span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Add Therapy Modal -->
                <div id="addTherapyModal" style="display: none;" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3 text-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Új terápia hozzáadása</h3>
                            <div class="mt-2 px-7 py-3">
                                <form action="/" method="post">
                                    <input type="hidden" name="action" value="add_therapy">
                                    <input type="text" name="patient" placeholder="Beteg neve" class="w-full px-4 py-2 border rounded-lg">
                                    <input type="text" name="type" placeholder="Terápia típusa" class="w-full mt-2 px-4 py-2 border rounded-lg">
                                    <select name="status" class="w-full mt-2 px-4 py-2 border rounded-lg">
                                        <option value="pending">Függőben</option>
                                        <option value="active">Aktív</option>
                                        <option value="completed">Befejezve</option>
                                    </select>
                                    <div class="items-center px-4 py-3">
                                        <button id="ok-btn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                            Hozzáadás
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="items-center px-4 py-3">
                                <button onclick="document.getElementById('addTherapyModal').style.display = 'none'" class="px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    Mégse
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medications View -->
                <div id="medicationsView" class="view" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-8">Gyógyszerek</h1>
                    <div class="space-y-4">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM medications");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($row['name']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['info']); ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Készlet: <?php echo htmlspecialchars($row['stock']); ?></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Notifications View -->
                <div id="notificationsView" class="view" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-8">Értesítések</h1>
                    <div class="space-y-4">
                        <div class="urgent-notification p-3 rounded-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path></svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Gyógyszer beadás esedékes - Kovács János</p>
                                    <p class="text-xs text-red-600 mt-1">10:30</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path></svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-800">Terápiás esemény befejezve - Nagy Anna</p>
                                    <p class="text-xs text-blue-600 mt-1">09:15</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patients View -->
                <div id="patientsView" class="view" style="display: none;">
                    <h1 class="text-3xl font-bold text-gray-800 mb-8">Betegek</h1>
                    <div class="space-y-4">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM patients");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium"><?php echo htmlspecialchars($row['name']); ?></p>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['diagnosis']); ?></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><?php echo htmlspecialchars($row['age']); ?> év</span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
// MOCK ADATOK
const mockUsers = {
  admin: { name: 'Kiss Béla', role: 'admin' },
  caregiver: { name: 'Gondozó Mária', role: 'caregiver' },
  pharmacist: { name: 'Dr. Patika', role: 'pharmacist' },
  sponsor: { name: 'Támogató Zrt.', role: 'sponsor' }
};
const mockTherapies = [
  { patient: 'Kovács János', type: 'Fizikoterápia', status: 'active' },
  { patient: 'Nagy Anna', type: 'Gyógytorna', status: 'completed' },
  { patient: 'Tóth Mária', type: 'Beszédterápia', status: 'pending' }
];
const mockMedications = [
  { name: 'Algopyrin', info: 'Fájdalomcsillapító', stock: 10 },
  { name: 'No-Spa', info: 'Görcsoldó', stock: 5 },
  { name: 'C-vitamin', info: 'Immunerősítő', stock: 20 }
];
const mockNotifications = [
  { urgent: true, text: 'Gyógyszer beadás esedékes - Kovács János', time: '10:30' },
  { urgent: false, text: 'Terápiás esemény befejezve - Nagy Anna', time: '09:15' }
];
const mockPatients = [
  { name: 'Kovács János', age: 67, diagnosis: 'Stroke utáni rehabilitáció' },
  { name: 'Nagy Anna', age: 74, diagnosis: 'Csípőprotézis' },
  { name: 'Tóth Mária', age: 80, diagnosis: 'Afázia' }
];
let mockChat = [
  { sender: 'Dr. Szabó Péter', text: 'Jó reggelt! Hogyan érzi magát Kovács János ma?', time: '08:45' },
  { sender: 'Gondozó Maria', text: 'Jó reggelt! Jól van, a gyógyszereket rendesen szedi.', time: '08:50' }
];

// ÁLLAPOT
let currentView = 'dashboard';

// SEGÉDFÜGGVÉNYEK
function showView(view) {
  currentView = view;
  document.querySelectorAll('.view').forEach(v => v.style.display = 'none');
  if (view === 'dashboard') document.getElementById('dashboardView').style.display = '';
  if (view === 'chat') {
    document.getElementById('chatView').style.display = '';
    renderChat();
  }
  if (view === 'therapy') {
    document.getElementById('therapyView').style.display = '';
    renderTherapies();
  }
  if (view === 'medications') {
    document.getElementById('medicationsView')?.remove();
    renderMedications();
  }
  if (view === 'notifications') {
    document.getElementById('notificationsView')?.remove();
    renderNotifications();
  }
  if (view === 'patients') {
    document.getElementById('patientsView')?.remove();
    renderPatients();
  }
}

// CHAT
function renderChat() {
  const chatDiv = document.getElementById('chatMessages');
  if (!chatDiv) return;
  chatDiv.innerHTML = '<div class="space-y-4">' +
    mockChat.map(msg => `
      <div class="flex justify-start">
        <div class="message-bubble bg-gray-100 text-gray-800 px-4 py-2 rounded-lg">
          <p class="text-sm font-medium mb-1">${msg.sender}</p>
          <p>${msg.text}</p>
          <p class="text-xs mt-1 opacity-75">${msg.time}</p>
        </div>
      </div>
    `).join('') + '</div>';
}
function sendMessage() {
  const input = document.getElementById('messageInput');
  if (!input.value.trim()) return;
  const now = new Date();
  mockChat.push({ sender: currentUser.name, text: input.value, time: now.toLocaleTimeString('hu-HU', { hour: '2-digit', minute: '2-digit' }) });
  input.value = '';
  renderChat();
}
function handleEnter(e) {
  if (e.key === 'Enter') sendMessage();
}

// TERÁPIÁK
function renderTherapies() {
  let therapyView = document.getElementById('therapyView');
  if (!therapyView) return;
  let html = `<div class="flex justify-between items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Terápiák</h1>
    <button onclick="addTherapy()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover-scale">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
      Új terápia
    </button>
  </div>`;
  html += '<div class="space-y-4">' +
    mockTherapies.map(t => `
      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
        <div>
          <p class="font-medium">${t.patient}</p>
          <p class="text-sm text-gray-600">${t.type}</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-medium status-${t.status}">
          ${t.status === 'active' ? 'Aktív' : t.status === 'completed' ? 'Befejezve' : 'Függőben'}
        </span>
      </div>
    `).join('') + '</div>';
  therapyView.innerHTML = html;
}
function addTherapy() {
  const name = prompt('Beteg neve:');
  const type = prompt('Terápia típusa:');
  if (name && type) {
    mockTherapies.push({ patient: name, type, status: 'pending' });
    renderTherapies();
  }
}

// GYÓGYSZEREK
function renderMedications() {
  let main = document.querySelector('.main-content');
  let div = document.createElement('div');
  div.id = 'medicationsView';
  div.className = 'view';
  let html = `<h1 class="text-3xl font-bold text-gray-800 mb-8">Gyógyszerek</h1>`;
  html += '<div class="space-y-4">' +
    mockMedications.map(m => `
      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
        <div>
          <p class="font-medium">${m.name}</p>
          <p class="text-sm text-gray-600">${m.info}</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Készlet: ${m.stock}</span>
      </div>
    `).join('') + '</div>';
  div.innerHTML = html;
  main.appendChild(div);
}

// ÉRTESÍTÉSEK
function renderNotifications() {
  let main = document.querySelector('.main-content');
  let div = document.createElement('div');
  div.id = 'notificationsView';
  div.className = 'view';
  let html = `<h1 class="text-3xl font-bold text-gray-800 mb-8">Értesítések</h1>`;
  html += '<div class="space-y-4">' +
    mockNotifications.map(n => `
      <div class="${n.urgent ? 'urgent-notification' : 'p-3 bg-blue-50 border-l-4 border-blue-500'} p-3 rounded-lg">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 ${n.urgent ? 'text-red-600' : 'text-blue-600'} mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path></svg>
          <div>
            <p class="text-sm font-medium ${n.urgent ? 'text-red-800' : 'text-blue-800'}">${n.text}</p>
            <p class="text-xs ${n.urgent ? 'text-red-600' : 'text-blue-600'} mt-1">${n.time}</p>
          </div>
        </div>
      </div>
    `).join('') + '</div>';
  div.innerHTML = html;
  main.appendChild(div);
}

// BETEGEK
function renderPatients() {
  let main = document.querySelector('.main-content');
  let div = document.createElement('div');
  div.id = 'patientsView';
  div.className = 'view';
  let html = `<h1 class="text-3xl font-bold text-gray-800 mb-8">Betegek</h1>`;
  html += '<div class="space-y-4">' +
    mockPatients.map(p => `
      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
        <div>
          <p class="font-medium">${p.name}</p>
          <p class="text-sm text-gray-600">${p.diagnosis}</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">${p.age} év</span>
      </div>
    `).join('') + '</div>';
  div.innerHTML = html;
  main.appendChild(div);
}

// ALAPÉRTELMEZETT NÉZET
window.onload = function() {
  // Eseménykezelők (ha újra renderelünk, ne legyen duplikált)
  window.showView = showView;
  window.sendMessage = sendMessage;
  window.handleEnter = handleEnter;
  window.addTherapy = addTherapy;

  <?php if (isset($_SESSION['user'])): ?>
  // Ha be van jelentkezve a felhasználó
  document.getElementById('loginScreen').style.display = 'none';
  document.getElementById('mainApp').style.display = '';
  document.getElementById('userName').textContent = '<?php echo $_SESSION['user']['name']; ?>';
  document.getElementById('userRole').textContent = '<?php echo $_SESSION['user']['role']; ?>';

  // Menü szerepkörhöz
  const userRole = '<?php echo $_SESSION['user']['role']; ?>';
  document.getElementById('therapyBtn').style.display = (userRole === 'admin' || userRole === 'caregiver') ? '' : 'none';
  document.getElementById('medicationsBtn').style.display = (userRole === 'admin' || userRole === 'pharmacist') ? '' : 'none';
  document.getElementById('patientsBtn').style.display = (userRole === 'admin' || userRole === 'caregiver') ? '' : 'none';

  showView('dashboard');
  <?php else: ?>
  // Ha nincs bejelentkezve a felhasználó
  document.getElementById('mainApp').style.display = 'none';
  document.getElementById('loginScreen').style.display = '';
  <?php endif; ?>
};
</script>
</body>
</html>
