<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMI Phone Locker - Control Console</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (Tailwind CDN as fallback/supplement to make styling dynamic and responsive) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            background: radial-gradient(circle at top right, #111827, #030712);
            font-family: 'Outfit', sans-serif;
            color: #F3F4F6;
        }
        
        .glass {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .phone-mockup {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 
                        0 0 40px rgba(79, 70, 229, 0.15);
            border: 12px solid #1F2937;
            border-radius: 40px;
        }

        .phone-screen {
            border-radius: 28px;
            overflow: hidden;
        }

        /* Pulse Animations */
        @keyframes pulse-red {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.03); opacity: 0.9; }
        }

        .pulse-lock-title {
            animation: pulse-red 2s infinite ease-in-out;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(31, 41, 55, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.3);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.5);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">
    <!-- Header -->
    <header class="glass sticky top-0 z-50 w-full px-6 py-4 flex items-center justify-between shadow-lg">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <!-- SVG Padlock Shield -->
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent">EMI Phone Locking Console</h1>
                <p class="text-xs text-indigo-400 font-medium">DPC Remote Device Policy Controller Backend</p>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <button onclick="openProvisioningModal()" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 transition duration-200 text-xs font-semibold text-white shadow-lg shadow-indigo-600/20 flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                QR Provision
            </button>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 mr-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Backend Live
            </span>
            <button onclick="fetchData()" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition duration-200 text-gray-400 hover:text-white" title="Force Sync Data">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.25"></path>
                </svg>
            </button>
        </div>
    </header>

    <!-- Main Content Grid -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-6 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Side: Admin Management Console (7 cols) -->
        <section class="lg:col-span-7 flex flex-col space-y-6">
            
            <!-- Dashboard Stats Summary -->
            <div class="grid grid-cols-3 gap-4">
                <div class="glass rounded-2xl p-4 flex flex-col justify-between">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Total Devices</span>
                    <span id="stat-total" class="text-3xl font-bold mt-2">0</span>
                </div>
                <div class="glass rounded-2xl p-4 flex flex-col justify-between border-l-2 border-l-rose-500">
                    <span class="text-xs font-semibold text-rose-400 uppercase">Locked</span>
                    <span id="stat-locked" class="text-3xl font-bold mt-2 text-rose-500">0</span>
                </div>
                <div class="glass rounded-2xl p-4 flex flex-col justify-between border-l-2 border-l-emerald-500">
                    <span class="text-xs font-semibold text-emerald-400 uppercase">Active / Ok</span>
                    <span id="stat-unlocked" class="text-3xl font-bold mt-2 text-emerald-500">0</span>
                </div>
            </div>

            <!-- Devices List Panel -->
            <div class="glass rounded-2xl p-6 shadow-xl flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Registered Devices
                    </h2>
                    
                    <!-- Search input -->
                    <input type="text" id="search-input" onkeyup="filterDevices()" placeholder="Search by name/IMEI..." class="px-3 py-1.5 bg-gray-900 border border-gray-700 rounded-lg text-sm focus:outline-none focus:border-indigo-500 text-white w-48 transition">
                </div>

                <!-- Devices Table -->
                <div class="overflow-x-auto flex-1 max-h-[360px]">
                    <table class="min-w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-800 text-xs font-semibold text-gray-400 uppercase">
                                <th class="py-3 px-2">Customer / Phone</th>
                                <th class="py-3 px-2">Device info</th>
                                <th class="py-3 px-2">Status</th>
                                <th class="py-3 px-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="devices-table-body" class="divide-y divide-gray-800/50 text-sm">
                            <!-- Populated dynamically -->
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">Loading devices from database...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Register New Mock Device Form -->
            <div class="glass rounded-2xl p-6 shadow-xl">
                <h3 class="text-md font-bold mb-4 flex items-center text-indigo-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    Register New DPC Client (Simulated Device)
                </h3>
                <form id="register-device-form" onsubmit="registerDevice(event)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Customer Name</label>
                        <input type="text" name="name" required placeholder="e.g. John Doe" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Customer Phone</label>
                        <input type="text" name="phone" required placeholder="e.g. +91 99999 11111" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Device Brand & Model</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="brand" required placeholder="Samsung" class="px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
                            <input type="text" name="model" required placeholder="Galaxy A35" class="px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Device IMEI 1 (15 Digits)</label>
                        <input type="text" name="imei" required maxlength="15" minlength="15" placeholder="e.g. 358201948201100" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">EMI Installment Amount (INR)</label>
                        <input type="number" name="emi_amount" required min="1" placeholder="1500" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Initial Status & Due Date</label>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="status" class="px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
                                <option value="UNLOCKED" selected>UNLOCKED (Due in future)</option>
                                <option value="LOCKED">LOCKED (Overdue now)</option>
                            </select>
                            <input type="date" name="due_date" required class="px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="md:col-span-2 pt-2">
                        <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 transition rounded-lg text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Provision & Register Device
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Right Side: Phone Simulator (5 cols) -->
        <section class="lg:col-span-5 flex flex-col items-center">
            
            <!-- Section Info -->
            <div class="w-full text-center lg:text-left mb-4 px-2">
                <h2 class="text-md font-bold text-white flex items-center justify-center lg:justify-start">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 mr-2 animate-ping"></span>
                    Interactive Device Simulator
                </h2>
                <p class="text-xs text-gray-400 mt-1">Select a device from the list on the left to sync this screen.</p>
            </div>

            <!-- Virtual Smartphone Frame -->
            <div class="w-[320px] h-[640px] phone-mockup bg-gray-900 p-0 flex flex-col relative transition-all duration-300">
                
                <!-- Front Camera Notch -->
                <div class="absolute top-2 left-1/2 -translate-x-1/2 w-32 h-5 bg-black rounded-full z-30 flex items-center justify-center">
                    <span class="w-2 h-2 rounded-full bg-blue-950/60 border border-blue-900/30"></span>
                </div>
                
                <!-- Phone Inner Screen -->
                <div id="sim-screen" class="phone-screen w-full h-full relative flex flex-col select-none transition-all duration-500">
                    
                    <!-- Simulated Status Bar -->
                    <div class="h-8 pt-2.5 px-6 flex justify-between items-center text-[10px] font-bold z-40 relative text-white">
                        <span id="sim-time">05:30</span>
                        <div class="flex items-center space-x-1.5">
                            <!-- Wifi Icon -->
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 21l-12-12c2-2 4.5-3 12-3s10 1 12 3l-12 12z"></path>
                            </svg>
                            <!-- Cellular Icon -->
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 22h20v-20z"></path>
                            </svg>
                            <!-- Battery Icon -->
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17 5H3a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2zM21 9h2v6h-2V9z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Screen Wallpaper Content (Switches dynamically) -->
                    <div id="sim-content" class="flex-1 flex flex-col relative p-6 pt-4 text-center justify-between z-20">
                        <!-- Simulated Lock Task screen will render here -->
                    </div>
                </div>

                <!-- Home Indicator Gesture Bar -->
                <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-28 h-1 bg-white/40 rounded-full z-30"></div>
            </div>

            <!-- Simulator Helper Controls below phone -->
            <div id="sim-controls" class="mt-4 glass rounded-xl p-4 w-[320px] text-xs text-gray-400 space-y-2 border border-dashed border-gray-800">
                <span class="font-bold text-white block uppercase text-[10px] tracking-wider">Simulator Help</span>
                <p>📍 Select any device in the list to load it.</p>
                <p>💡 Tap the <strong>"DEVICE LOCKED"</strong> title <strong>5 times</strong> in the simulator to unlock using bypass code.</p>
                <p>💳 Clicking <strong>"Pay Instantly On Device"</strong> simulates a successful payment webhook response from UPI gate.</p>
            </div>
        </section>
    </main>

    <!-- Slide Up Modal for Bypass Code Entry -->
    <div id="bypass-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="glass max-w-sm w-full rounded-2xl p-6 shadow-2xl animate-fade-in border border-indigo-500/30">
            <h4 class="text-md font-bold mb-2 text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Enter DPC Bypass Code
            </h4>
            <p class="text-xs text-gray-400 mb-4">Input the admin offline clearance key synced on setup. (Default is <code class="text-indigo-400 bg-indigo-500/10 px-1 rounded">998877</code> or device specific).</p>
            
            <input type="password" id="bypass-code-input" maxlength="8" placeholder="Enter code" class="w-full text-center py-3 bg-gray-900 border border-gray-700 rounded-xl text-lg tracking-widest text-white focus:outline-none focus:border-indigo-500 mb-4 font-bold">
            
            <div id="bypass-error" class="text-xs text-rose-500 mb-4 hidden text-center font-semibold">❌ Invalid bypass code. Please check console database.</div>
            
            <div class="grid grid-cols-2 gap-3">
                <button onclick="closeBypassModal()" class="py-2 bg-gray-800 hover:bg-gray-700 transition rounded-lg text-xs font-semibold text-gray-300">Cancel</button>
                <button onclick="verifyBypassCode()" class="py-2 bg-indigo-600 hover:bg-indigo-500 transition rounded-lg text-xs font-semibold text-white shadow-md shadow-indigo-600/20">Verify & Bypass</button>
            </div>
        </div>
    </div>

    <!-- Active Installments Overlay Modal -->
    <div id="installments-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="glass max-w-lg w-full rounded-2xl p-6 shadow-2xl border border-gray-800 flex flex-col max-h-[80vh]">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-md font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    EMI Ledger for <span id="ledger-customer-name" class="ml-1.5 text-indigo-400 font-semibold">Customer</span>
                </h4>
                <button onclick="closeInstallmentsModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="overflow-y-auto flex-1 mb-4">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-800 text-xs font-semibold text-gray-400 uppercase">
                            <th class="py-2">Due Date</th>
                            <th class="py-2">Amount</th>
                            <th class="py-2">Status</th>
                            <th class="py-2 text-right">Payment Capture</th>
                        </tr>
                    </thead>
                    <tbody id="ledger-table-body" class="text-sm divide-y divide-gray-800/40">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- QR Provisioning Modal -->
    <div id="provisioning-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="glass max-w-sm w-full rounded-2xl p-6 shadow-2xl border border-indigo-500/30 text-center relative">
            <button onclick="closeProvisioningModal()" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            
            <h4 class="text-md font-bold mb-2 text-white">Android Enterprise QR</h4>
            <p class="text-xs text-gray-400 mb-6">Scan this on the "Hello" screen of a factory-reset device (Tap 6 times to open scanner).</p>
            
            <div id="qr-loading" class="text-sm text-indigo-400 animate-pulse mb-6">Generating secure payload...</div>
            
            <div class="flex justify-center bg-white p-3 rounded-xl mx-auto w-fit hidden" id="qr-container">
                <img id="qr-image" src="" alt="Provisioning QR Code" class="w-48 h-48 pointer-events-none" />
            </div>
            
            <div id="qr-error" class="text-xs text-rose-500 mt-4 hidden"></div>
        </div>
    </div>

    <!-- Alert Banner -->
    <div id="toast" class="fixed bottom-6 right-6 px-4 py-3 rounded-xl shadow-2xl glass border border-indigo-500/30 flex items-center space-x-2 text-sm text-white transform translate-y-20 opacity-0 transition-all duration-300 z-50">
        <span id="toast-icon">✨</span>
        <span id="toast-message">Message details</span>
    </div>

    <!-- Footer -->
    <footer class="glass py-4 text-center text-xs text-gray-500 w-full mt-auto border-t border-gray-900">
        © 2026 EMI Phone Locking System. All rights reserved. Powered by Laravel 11.
    </footer>

    <!-- Scripts -->
    <script>
        // State variables
        let allDevices = [];
        let activeDevice = null;
        let lockTitleTapCount = 0;

        // Auto-refresh timer
        let pollInterval = null;

        // On Load
        window.addEventListener('DOMContentLoaded', () => {
            // Set Clock
            updateSimTime();
            setInterval(updateSimTime, 1000);

            // Fetch initial data
            fetchData();
            
            // Setup polling every 3 seconds for live sync
            pollInterval = setInterval(fetchData, 3000);
            
            // Set today as default in register form date picker
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="due_date"]').value = today;
        });

        // Set time inside simulated phone status bar
        function updateSimTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('sim-time').innerText = `${hours}:${minutes}`;
        }

        // Fetch Dashboard Data
        function fetchData() {
            fetch('/api/dashboard/data')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        allDevices = data.devices;
                        updateStats();
                        renderDevicesTable();
                        
                        // Sync Active Device state in Simulator
                        if (activeDevice) {
                            const updated = allDevices.find(d => d.id === activeDevice.id);
                            if (updated) {
                                activeDevice = updated;
                                renderSimulator();
                            }
                        } else if (allDevices.length > 0) {
                            // Default select first device
                            activeDevice = allDevices[0];
                            renderSimulator();
                        } else {
                            renderEmptySimulator();
                        }
                    }
                })
                .catch(err => {
                    console.error("Error fetching dashboard data:", err);
                    showToast("❌ Connection error to backend server.", "rose");
                });
        }

        // Update Dashboard Stats Counters
        function updateStats() {
            document.getElementById('stat-total').innerText = allDevices.length;
            const lockedCount = allDevices.filter(d => d.status === 'LOCKED').length;
            document.getElementById('stat-locked').innerText = lockedCount;
            document.getElementById('stat-unlocked').innerText = allDevices.length - lockedCount;
        }

        // Render Devices Table HTML
        function renderDevicesTable() {
            const tbody = document.getElementById('devices-table-body');
            const query = document.getElementById('search-input').value.toLowerCase();
            
            const filtered = allDevices.filter(d => {
                const customerName = (d.customer?.name || '').toLowerCase();
                const imei = (d.imei_1 || '').toLowerCase();
                const model = (d.brand + ' ' + d.model).toLowerCase();
                return customerName.includes(query) || imei.includes(query) || model.includes(query);
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="py-8 text-center text-gray-500">No matching devices found.</td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.map(device => {
                const isActive = activeDevice && activeDevice.id === device.id;
                const statusBadge = device.status === 'LOCKED' 
                    ? `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">LOCKED</span>`
                    : `<span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">ACTIVE</span>`;

                return `
                    <tr class="hover:bg-gray-800/30 transition duration-150 ${isActive ? 'bg-indigo-500/5 border-l-2 border-indigo-500' : ''}">
                        <td class="py-3 px-2">
                            <div class="font-bold text-white">${device.customer?.name || 'N/A'}</div>
                            <div class="text-xs text-gray-400">${device.customer?.phone || 'No Phone'}</div>
                        </td>
                        <td class="py-3 px-2">
                            <div class="font-medium text-gray-300">${device.brand} ${device.model}</div>
                            <div class="text-xs font-mono text-indigo-400/80">${device.imei_1}</div>
                        </td>
                        <td class="py-3 px-2">${statusBadge}</td>
                        <td class="py-3 px-2 text-right space-x-1.5 whitespace-nowrap">
                            <button onclick="selectDevice(${device.id})" class="px-2.5 py-1 rounded bg-indigo-600 hover:bg-indigo-500 text-xs text-white font-medium transition duration-200">
                                View
                            </button>
                            <button onclick="openLedger(${device.id})" class="px-2.5 py-1 rounded bg-gray-800 hover:bg-gray-700 text-xs text-gray-300 font-medium transition duration-200" title="View installments">
                                EMI Ledger
                            </button>
                            ${device.status === 'UNLOCKED' 
                                ? `<button onclick="lockDevice(${device.id})" class="px-2 py-1 rounded bg-rose-950/40 hover:bg-rose-900/40 border border-rose-800/30 text-xs text-rose-400 font-medium transition duration-200">Lock</button>`
                                : `<button onclick="unlockDevice(${device.id})" class="px-2 py-1 rounded bg-emerald-950/40 hover:bg-emerald-900/40 border border-emerald-800/30 text-xs text-emerald-400 font-medium transition duration-200">Unlock</button>`
                            }
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Search Filters
        function filterDevices() {
            renderDevicesTable();
        }

        // Select Device to display inside Simulator
        function selectDevice(id) {
            const dev = allDevices.find(d => d.id === id);
            if (dev) {
                activeDevice = dev;
                lockTitleTapCount = 0;
                renderSimulator();
                renderDevicesTable();
                showToast(`📲 Loading ${dev.brand} ${dev.model} in simulator`, 'indigo');
            }
        }

        // Lock Device Command
        function lockDevice(id) {
            showToast("⏳ Dispatching Remote Lock Signal...", "indigo");
            fetch(`/api/dashboard/device/${id}/lock`, { method: 'POST', headers: { 'Content-Type': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showToast(`🔒 ${data.message}`, 'rose');
                        fetchData();
                    } else {
                        showToast(`❌ ${data.message}`, 'rose');
                    }
                })
                .catch(() => showToast("❌ Server connection lost.", "rose"));
        }

        // Unlock Device Command
        function unlockDevice(id) {
            showToast("⏳ Dispatching Remote Unlock Signal...", "indigo");
            fetch(`/api/dashboard/device/${id}/unlock`, { method: 'POST', headers: { 'Content-Type': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showToast(`🔓 ${data.message}`, 'emerald');
                        fetchData();
                    } else {
                        showToast(`❌ ${data.message}`, 'rose');
                    }
                })
                .catch(() => showToast("❌ Server connection lost.", "rose"));
        }

        // Register New Simulated Device Form Submission
        function registerDevice(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const bodyObj = {};
            formData.forEach((value, key) => bodyObj[key] = value);

            fetch('/api/dashboard/device/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bodyObj)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast("🎉 Mock DPC Client Registered Successfully!", "emerald");
                    e.target.reset();
                    // Set default date again
                    const today = new Date().toISOString().split('T')[0];
                    document.querySelector('input[name="due_date"]').value = today;
                    
                    fetchData();
                } else {
                    showToast(`❌ Registration failed.`, 'rose');
                }
            })
            .catch(() => showToast("❌ Server connection lost.", "rose"));
        }

        // Render Simulator Content based on current status
        function renderSimulator() {
            const screen = document.getElementById('sim-screen');
            const content = document.getElementById('sim-content');
            
            if (!activeDevice) {
                renderEmptySimulator();
                return;
            }

            if (activeDevice.status === 'LOCKED') {
                // RED LOCK SCREEN
                screen.style.background = '#0F172A'; // Slate-dark
                
                content.innerHTML = `
                    <div class="flex flex-col items-center flex-1 justify-between pt-6">
                        <!-- Red Pulsing Shield Title -->
                        <div onclick="handleTitleTap()" class="cursor-pointer flex flex-col items-center">
                            <div class="w-12 h-12 bg-rose-500/15 border border-rose-500/30 rounded-full flex items-center justify-center text-rose-500 mb-2 shadow-lg shadow-rose-500/10 pulse-lock-title">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <h3 id="sim-lock-title" class="text-rose-500 font-bold text-sm tracking-widest uppercase">DEVICE LOCKED</h3>
                            <p class="text-[9px] text-gray-500 mt-1">Managed by Retailer DPC Owner</p>
                        </div>

                        <!-- Loan Info Card -->
                        <div class="w-full bg-gray-800/80 border border-gray-700/50 rounded-xl p-3.5 mt-2 text-left shadow-lg">
                            <span class="text-[8px] font-bold text-indigo-400 uppercase tracking-widest block mb-1">Customer Account</span>
                            <div class="font-bold text-gray-200 text-xs truncate">${activeDevice.customer?.name || 'Loading Name...'}</div>
                            <div class="text-[10px] text-gray-400 mt-1 flex justify-between">
                                <span>EMI Amount Due:</span>
                                <span class="font-bold text-white text-xs">₹${Number(activeDevice.amount_due).toFixed(2)}</span>
                            </div>
                        </div>

                        <!-- Scan QR Section -->
                        <div class="flex flex-col items-center mt-3 bg-white p-2 rounded-lg shadow-inner">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=upi://pay?pa=${activeDevice.upi_id || 'merchant@upi'}&pn=EMILocker&am=${activeDevice.amount_due}&cu=INR" 
                                 alt="UPI QR Code" class="w-24 h-24 select-none pointer-events-none" />
                        </div>
                        <p class="text-[9px] text-gray-400 mt-1">Scan QR with GPay / PhonePe / Paytm</p>

                        <!-- Actions Buttons -->
                        <div class="w-full space-y-2 mt-4">
                            <button onclick="triggerSimPayment()" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 transition rounded-lg text-xs font-bold text-white shadow-lg shadow-emerald-600/20">
                                Pay Instantly On Device
                            </button>
                            <button onclick="simulateEmergencyCall()" class="w-full py-1.5 bg-gray-800 hover:bg-gray-700 transition rounded-lg text-[10px] font-semibold text-gray-300">
                                Emergency Call (112)
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // ACTIVE HOME SCREEN
                // Twilight sky gradient
                screen.style.background = 'linear-gradient(180deg, #312E81 0%, #1E1B4B 50%, #030712 100%)'; 
                
                content.innerHTML = `
                    <div class="flex flex-col items-center flex-1 justify-between pt-8">
                        <!-- Clock & Date Widget -->
                        <div class="text-center">
                            <div class="text-3xl font-light tracking-wide text-white">${document.getElementById('sim-time').innerText}</div>
                            <div class="text-[10px] text-indigo-200 font-medium mt-1">Thursday, June 18</div>
                            <span class="inline-flex items-center mt-3 px-2 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <span class="w-1.5 h-1.5 mr-1 rounded-full bg-emerald-400"></span>
                                Device Unlocked
                            </span>
                        </div>

                        <!-- App Grid -->
                        <div class="grid grid-cols-4 gap-4 w-full px-2 mt-6">
                            <div onclick="showToast('⚙️ Launcher Settings are managed by DPC Owner.', 'indigo')" class="flex flex-col items-center cursor-pointer group">
                                <div class="w-10 h-10 rounded-xl bg-gray-800 border border-gray-700 flex items-center justify-center text-white group-hover:scale-105 transition shadow-md">
                                    ⚙️
                                </div>
                                <span class="text-[9px] text-indigo-200 mt-1 truncate w-full">Settings</span>
                            </div>
                            <div onclick="showToast('🌐 Opening simulated Google Chrome...', 'indigo')" class="flex flex-col items-center cursor-pointer group">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white group-hover:scale-105 transition shadow-md">
                                    🌐
                                </div>
                                <span class="text-[9px] text-indigo-200 mt-1 truncate w-full">Chrome</span>
                            </div>
                            <div onclick="showToast('📷 Camera active and fully enabled.', 'indigo')" class="flex flex-col items-center cursor-pointer group">
                                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white group-hover:scale-105 transition shadow-md">
                                    📷
                                </div>
                                <span class="text-[9px] text-indigo-200 mt-1 truncate w-full">Camera</span>
                            </div>
                            <div onclick="showToast('📞 Dialing keypad is active.', 'indigo')" class="flex flex-col items-center cursor-pointer group">
                                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white group-hover:scale-105 transition shadow-md">
                                    📞
                                </div>
                                <span class="text-[9px] text-indigo-200 mt-1 truncate w-full">Phone</span>
                            </div>
                        </div>

                        <!-- Empty space spacer -->
                        <div class="flex-1"></div>

                        <!-- DPC Brand tag at bottom -->
                        <div class="w-full bg-white/5 border border-white/10 rounded-xl p-2.5 text-center shadow-lg">
                            <div class="text-[9px] font-bold text-gray-300">🛡️ Device Protected</div>
                            <div class="text-[8px] text-gray-400 mt-0.5 font-mono truncate">IMEI: ${activeDevice.imei_1}</div>
                        </div>
                    </div>
                `;
            }
        }

        // Empty state simulator
        function renderEmptySimulator() {
            const screen = document.getElementById('sim-screen');
            screen.style.background = '#0F172A';
            const content = document.getElementById('sim-content');
            content.innerHTML = `
                <div class="flex flex-col items-center justify-center flex-1 text-center px-4 space-y-3">
                    <span class="text-4xl">📴</span>
                    <h3 class="text-sm font-bold text-white">No Selected Device</h3>
                    <p class="text-xs text-gray-400">Register or select a device from the left panel to load the interactive mobile screen preview.</p>
                </div>
            `;
        }

        // Handle title tap (5 times opens bypass code input)
        function handleTitleTap() {
            lockTitleTapCount++;
            if (lockTitleTapCount >= 5) {
                lockTitleTapCount = 0;
                openBypassModal();
            } else {
                showToast(`🔑 Tap title ${5 - lockTitleTapCount} more times for bypass modal`, 'indigo');
            }
        }

        // Bypass Code Modal Control
        function openBypassModal() {
            document.getElementById('bypass-modal').classList.remove('hidden');
            document.getElementById('bypass-code-input').value = '';
            document.getElementById('bypass-error').classList.add('hidden');
            document.getElementById('bypass-code-input').focus();
        }

        function closeBypassModal() {
            document.getElementById('bypass-modal').classList.add('hidden');
        }

        function verifyBypassCode() {
            const input = document.getElementById('bypass-code-input').value;
            const correctCode = activeDevice.bypass_code || '998877';

            if (input === correctCode) {
                closeBypassModal();
                showToast("✅ Bypass code verified! Unlocking device locally...", "emerald");
                
                // Perform AJAX unlock locally
                unlockDevice(activeDevice.id);
            } else {
                document.getElementById('bypass-error').classList.remove('hidden');
            }
        }

        // Trigger Simulated Webhook Payment
        function triggerSimPayment() {
            if (!activeDevice) return;
            
            showToast("💸 Sending Simulated Payment webhook...", "indigo");
            
            fetch('/api/payment/mock-trigger', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    imei: activeDevice.imei_1,
                    amount: activeDevice.amount_due
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.unlocked) {
                        showToast("🎉 Payment captured & Device Unlocked remote call dispatched!", "emerald");
                    } else {
                        showToast("💸 Payment captured, but other installments are still overdue.", "warning");
                    }
                    fetchData();
                } else {
                    showToast("❌ Payment simulation failed.", "rose");
                }
            })
            .catch(() => showToast("❌ Server communication failed.", "rose"));
        }

        // Sim Dialing 112
        function simulateEmergencyCall() {
            showToast("🚨 Dialing emergency 112... Phone keypad active.", "rose");
        }

        // Ledger Modal Control
        function openLedger(deviceId) {
            const dev = allDevices.find(d => d.id === deviceId);
            if (!dev) return;

            document.getElementById('ledger-customer-name').innerText = dev.customer?.name || 'Customer';
            const tbody = document.getElementById('ledger-table-body');
            
            if (!dev.emi_installments || dev.emi_installments.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-gray-500">No installments found for this customer.</td></tr>`;
            } else {
                tbody.innerHTML = dev.emi_installments.map(emi => {
                    const statusClass = emi.status === 'PAID' 
                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                        : (new Date(emi.due_date) < new Date() ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20');
                    const statusText = emi.status === 'PAID' ? 'PAID' : (new Date(emi.due_date) < new Date() ? 'OVERDUE' : 'PENDING');
                    
                    const payBtn = emi.status === 'PENDING'
                        ? `<button onclick="captureLedgerPayment('${dev.imei_1}', ${emi.amount})" class="px-2 py-0.5 rounded bg-emerald-600 hover:bg-emerald-500 text-xs text-white transition">Capture</button>`
                        : `<span class="text-xs text-gray-500">—</span>`;

                    return `
                        <tr class="hover:bg-gray-800/10">
                            <td class="py-3 font-mono text-gray-300 text-xs">${emi.due_date}</td>
                            <td class="py-3 text-white font-semibold">₹${Number(emi.amount).toFixed(2)}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                    ${statusText}
                                </span>
                            </td>
                            <td class="py-3 text-right">${payBtn}</td>
                        </tr>
                    `;
                }).join('');
            }

            document.getElementById('installments-modal').classList.remove('hidden');
        }

        function closeInstallmentsModal() {
            document.getElementById('installments-modal').classList.add('hidden');
        }

        // Capture Ledger Payment mock
        function captureLedgerPayment(imei, amount) {
            showToast("💸 Capturing installment payment...", "indigo");
            fetch('/api/payment/mock-trigger', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ imei: imei, amount: amount })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast("🎉 Installment Paid! Device unlocked remotely.", "emerald");
                    closeInstallmentsModal();
                    fetchData();
                } else {
                    showToast("❌ Payment capture failed.", "rose");
                }
            })
            .catch(() => showToast("❌ Server error during capture.", "rose"));
        }

        // QR Provisioning Modal functions
        function openProvisioningModal() {
            document.getElementById('provisioning-modal').classList.remove('hidden');
            document.getElementById('qr-loading').classList.remove('hidden');
            document.getElementById('qr-container').classList.add('hidden');
            document.getElementById('qr-error').classList.add('hidden');

            fetch('/api/dashboard/provisioning-qr')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('qr-loading').classList.add('hidden');
                    if (data.status === 'success') {
                        const jsonPayload = JSON.stringify(data.provisioning_data);
                        const encodedUrl = encodeURIComponent(jsonPayload);
                        document.getElementById('qr-image').src = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodedUrl}`;
                        document.getElementById('qr-container').classList.remove('hidden');
                    } else {
                        document.getElementById('qr-error').innerText = data.message;
                        document.getElementById('qr-error').classList.add('hidden');
                        document.getElementById('qr-error').classList.remove('hidden');
                    }
                })
                .catch(() => {
                    document.getElementById('qr-loading').classList.add('hidden');
                    document.getElementById('qr-error').innerText = "Failed to fetch provisioning payload.";
                    document.getElementById('qr-error').classList.remove('hidden');
                });
        }

        function closeProvisioningModal() {
            document.getElementById('provisioning-modal').classList.add('hidden');
        }

        // Toast Messages Banner
        function showToast(message, type = 'indigo') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = document.getElementById('toast-icon');

            // Set message & styling
            toastMessage.innerText = message;
            
            // Border colors based on type
            toast.className = "fixed bottom-6 right-6 px-4 py-3 rounded-xl shadow-2xl glass flex items-center space-x-2 text-sm text-white transform transition-all duration-300 z-50 border";
            if (type === 'emerald') {
                toast.classList.add('border-emerald-500/40', 'shadow-emerald-500/5');
                toastIcon.innerText = "✅";
            } else if (type === 'rose') {
                toast.classList.add('border-rose-500/40', 'shadow-rose-500/5');
                toastIcon.innerText = "🔒";
            } else if (type === 'warning') {
                toast.classList.add('border-yellow-500/40', 'shadow-yellow-500/5');
                toastIcon.innerText = "⚠️";
            } else {
                toast.classList.add('border-indigo-500/40', 'shadow-indigo-500/5');
                toastIcon.innerText = "✨";
            }

            // Animate In
            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            // Animate Out after 3.5s
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3500);
        }
    </script>
</body>
</html>
