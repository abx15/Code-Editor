<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Code Editor with AI Help</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- CodeMirror CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .editor-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .CodeMirror {
            height: 400px !important;
            border-radius: 10px;
            font-size: 14px;
        }
        
        .ai-panel {
            background: #f8f9fa;
            border-radius: 10px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .suggestion-card {
            background: white;
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }
        
        .suggestion-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-online { background: #28a745; }
        .status-offline { background: #dc3545; }
        
        .floating-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border: none;
            box-shadow: 0 10px 25px rgba(0,123,255,0.3);
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 35px rgba(0,123,255,0.4);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="editor-container p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="fas fa-code text-primary"></i>
                            Live Code Editor
                        </h2>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-success">
                                <span class="status-indicator status-online"></span>
                                Session: <span id="sessionId">demo-session</span>
                            </span>
                            <span id="aiStatus" class="badge bg-warning">
                                <span class="status-indicator status-offline"></span>
                                AI: Checking...
                            </span>
                            <select id="languageSelect" class="form-select form-select-sm" style="width: auto;">
                                <option value="javascript">JavaScript</option>
                                <option value="python">Python</option>
                                <option value="php">PHP</option>
                                <option value="html">HTML</option>
                                <option value="css">CSS</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Code Editor Column -->
            <div class="col-lg-8">
                <div class="editor-container p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5><i class="fas fa-edit"></i> Code Editor</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="runCode">
                                <i class="fas fa-play"></i> Run
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" id="saveCode">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm" id="formatCode">
                                <i class="fas fa-magic"></i> Format
                            </button>
                        </div>
                    </div>
                    <textarea id="codeEditor" placeholder="Start coding here..."></textarea>
                    
                    <!-- Output Panel -->
                    <div class="mt-3">
                        <h6><i class="fas fa-terminal"></i> Output</h6>
                        <div id="outputPanel" class="bg-dark text-light p-3 rounded" style="height: 150px; overflow-y: auto; font-family: monospace;">
                            <div class="text-muted">Output will appear here...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Panel Column -->
            <div class="col-lg-4">
                <div class="editor-container p-4">
                    <h5><i class="fas fa-robot text-primary"></i> AI Assistant</h5>
                    
                    <!-- AI Action Buttons -->
                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-primary btn-sm" id="getAiSuggestion">
                            <i class="fas fa-lightbulb"></i> Get Code Suggestions
                        </button>
                        <button class="btn btn-warning btn-sm" id="fixCode">
                            <i class="fas fa-wrench"></i> Fix My Code
                        </button>
                        <button class="btn btn-info btn-sm" id="explainCode">
                            <i class="fas fa-question-circle"></i> Explain Code
                        </button>
                    </div>

                    <!-- AI Suggestions Panel -->
                    <div class="ai-panel p-3">
                        <div id="aiSuggestions">
                            <div class="text-center text-muted">
                                <i class="fas fa-robot fa-2x mb-2"></i>
                                <p>Click any AI button above to get smart suggestions!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Users Panel -->
                <div class="editor-container p-4 mt-3">
                    <h6><i class="fas fa-users"></i> Live Users</h6>
                    <div id="liveUsers">
                        <div class="d-flex align-items-center mb-2">
                            <span class="status-indicator status-online"></span>
                            <span>You (Active)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Help Button -->
    <button class="floating-btn" data-bs-toggle="modal" data-bs-target="#helpModal">
        <i class="fas fa-question"></i>
    </button>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">How to Use</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>Features:</h6>
                    <ul>
                        <li><strong>Real-time Collaboration:</strong> Multiple users can edit the same code</li>
                        <li><strong>AI Assistance:</strong> Get smart code suggestions and fixes</li>
                        <li><strong>Multiple Languages:</strong> JavaScript, Python, PHP, HTML, CSS</li>
                        <li><strong>Auto-save:</strong> Your code is saved automatically</li>
                    </ul>
                    <h6>Shortcuts:</h6>
                    <ul>
                        <li><kbd>Ctrl + S</kbd> - Save code</li>
                        <li><kbd>Ctrl + Enter</kbd> - Run code</li>
                        <li><kbd>Ctrl + /</kbd> - Toggle comment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/python/python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>

    <script>
        // Initialize CodeMirror
        let editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
            lineNumbers: true,
            mode: 'javascript',
            theme: 'dracula',
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 4,
            tabSize: 4,
            lineWrapping: true
        });

        let currentSession = 'demo-session';
        let autoSaveInterval;
        let isLoading = false;

        // Load initial code
        loadCode();

        // Language change handler
        document.getElementById('languageSelect').addEventListener('change', function() {
            const selectedLanguage = this.value;
            const modeMap = {
                'javascript': 'javascript',
                'python': 'python',
                'php': 'php',
                'html': 'xml',
                'css': 'css'
            };
            editor.setOption('mode', modeMap[selectedLanguage]);
        });

        // Auto-save functionality
        editor.on('change', function() {
            clearTimeout(autoSaveInterval);
            autoSaveInterval = setTimeout(function() {
                saveCode(false); // Silent save
            }, 2000);
        });

        // Button event listeners
        document.getElementById('saveCode').addEventListener('click', () => saveCode(true));
        document.getElementById('runCode').addEventListener('click', runCode);
        document.getElementById('formatCode').addEventListener('click', formatCode);
        document.getElementById('getAiSuggestion').addEventListener('click', () => getAiHelp('suggestion'));
        document.getElementById('fixCode').addEventListener('click', () => getAiHelp('fix'));
        document.getElementById('explainCode').addEventListener('click', () => getAiHelp('explain'));

        // Functions
        function loadCode() {
            fetch('php/get_code.php?session=' + currentSession)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        editor.setValue(data.code);
                        document.getElementById('languageSelect').value = data.language;
                    }
                })
                .catch(error => console.error('Error loading code:', error));
        }

        function saveCode(showMessage = true) {
            if (isLoading) return;
            
            const formData = new FormData();
            formData.append('session', currentSession);
            formData.append('code', editor.getValue());
            formData.append('language', document.getElementById('languageSelect').value);

            fetch('php/save_code.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (showMessage && data.success) {
                    showNotification('Code saved successfully!', 'success');
                }
            })
            .catch(error => console.error('Error saving code:', error));
        }

        function runCode() {
            const code = editor.getValue();
            const language = document.getElementById('languageSelect').value;
            const outputPanel = document.getElementById('outputPanel');
            
            if (language === 'javascript') {
                try {
                    // Capture console.log output
                    let output = '';
                    const originalLog = console.log;
                    console.log = function(...args) {
                        output += args.join(' ') + '\n';
                        originalLog.apply(console, arguments);
                    };
                    
                    eval(code);
                    console.log = originalLog;
                    
                    outputPanel.innerHTML = '<div class="text-success">' + (output || 'Code executed successfully!') + '</div>';
                } catch (error) {
                    outputPanel.innerHTML = '<div class="text-danger">Error: ' + error.message + '</div>';
                }
            } else {
                outputPanel.innerHTML = '<div class="text-warning">Code execution for ' + language + ' is not supported in browser environment.</div>';
            }
        }

        function formatCode() {
            const code = editor.getValue();
            // Basic formatting (you can enhance this)
            const formatted = code.replace(/;/g, ';\n').replace(/{/g, '{\n').replace(/}/g, '\n}');
            editor.setValue(formatted);
            showNotification('Code formatted!', 'info');
        }

        function getAiHelp(type) {
            if (isLoading) return;
            
            isLoading = true;
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            button.disabled = true;

            const formData = new FormData();
            formData.append('code', editor.getValue());
            formData.append('language', document.getElementById('languageSelect').value);
            formData.append('type', type);
            formData.append('session', currentSession);

            fetch('php/ai_helper.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayAiSuggestion(data.suggestion, type);
                } else {
                    displayAiSuggestion('Sorry, AI service is currently unavailable. Please try again later.', 'error');
                }
            })
            .catch(error => {
                displayAiSuggestion('Error connecting to AI service.', 'error');
            })
            .finally(() => {
                isLoading = false;
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function displayAiSuggestion(suggestion, type) {
            const suggestionsPanel = document.getElementById('aiSuggestions');
            const typeIcons = {
                'suggestion': 'fas fa-lightbulb',
                'fix': 'fas fa-wrench',
                'explain': 'fas fa-info-circle',
                'error': 'fas fa-exclamation-triangle'
            };
            
            const suggestionHtml = `
                <div class="suggestion-card p-3 mb-3 rounded">
                    <div class="d-flex align-items-start">
                        <i class="${typeIcons[type] || 'fas fa-robot'} text-primary me-2 mt-1"></i>
                        <div>
                            <small class="text-muted">${new Date().toLocaleTimeString()}</small>
                            <p class="mb-0">${suggestion}</p>
                        </div>
                    </div>
                </div>
            `;
            
            suggestionsPanel.innerHTML = suggestionHtml + suggestionsPanel.innerHTML;
        }

        function showNotification(message, type) {
            // Simple notification system
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
            notification.innerHTML = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                saveCode(true);
            } else if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                runCode();
            }
        });

        // Real-time sync (check for updates every 5 seconds)
        setInterval(function() {
            // In a real application, you would check for updates from other users
            // For now, we'll just update the timestamp
        }, 5000);
    </script>
</body>
</html>