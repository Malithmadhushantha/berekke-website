<?php
require_once 'config/config.php';
require_once 'config/ai_config.php';

// Require login
requireLogin();

$user = getUserInfo();
$page_title = "AI Legal Assistant";
include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-primary text-white rounded-4 p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="h2 mb-2">
                            <i class="fas fa-robot me-3"></i>
                            AI Legal Assistant
                        </h1>
                        <p class="mb-0 opacity-75">
                            Ask questions about Sri Lankan law - Penal Code, Criminal Procedure Code, and Evidence Ordinance
                        </p>
                    </div>
                    <div class="col-lg-4 text-end">
                        <i class="fas fa-brain fa-4x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chat Interface -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="height: 600px;">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        Legal AI Chat
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-light" onclick="clearChat()">
                            <i class="fas fa-trash me-1"></i>Clear
                        </button>
                        <button class="btn btn-sm btn-outline-light" onclick="exportChat()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                </div>
                
                <div class="card-body p-0 d-flex flex-column">
                    <!-- Chat Messages -->
                    <div id="chatMessages" class="flex-grow-1 p-3" style="overflow-y: auto; max-height: 450px;">
                        <!-- Welcome Message -->
                        <div class="message assistant-message mb-3">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content">
                                    <div class="bg-light rounded-3 p-3">
                                        <p class="mb-0">
                                            Welcome to the AI Legal Assistant! I can help you with questions about:
                                        </p>
                                        <ul class="mt-2 mb-0">
                                            <li>Sri Lankan Penal Code sections</li>
                                            <li>Criminal Procedure Code procedures</li>
                                            <li>Evidence Ordinance provisions</li>
                                            <li>Legal definitions and explanations</li>
                                            <li>Police procedures and protocols</li>
                                        </ul>
                                        <p class="mt-2 mb-0 text-muted small">
                                            Ask me anything in English or Sinhala!
                                        </p>
                                    </div>
                                    <small class="text-muted">Just now</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="border-top p-3">
                        <form id="chatForm" onsubmit="sendMessage(event)">
                            <div class="input-group">
                                <input type="text" class="form-control" id="messageInput" 
                                       placeholder="Ask about legal sections, procedures, or definitions..." 
                                       autocomplete="off" required>
                                <button class="btn btn-primary" type="submit" id="sendButton">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Try: "What is murder in section 296?", "How to arrest someone?", "Evidence admissibility rules"
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Questions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Quick Questions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm text-start" onclick="askQuickQuestion('What is the difference between murder and culpable homicide?')">
                            <i class="fas fa-gavel me-2"></i>Murder vs Culpable Homicide
                        </button>
                        <button class="btn btn-outline-success btn-sm text-start" onclick="askQuickQuestion('What are the procedures for arrest without warrant?')">
                            <i class="fas fa-handcuffs me-2"></i>Arrest Procedures
                        </button>
                        <button class="btn btn-outline-warning btn-sm text-start" onclick="askQuickQuestion('What types of evidence are admissible in court?')">
                            <i class="fas fa-search me-2"></i>Evidence Rules
                        </button>
                        <button class="btn btn-outline-info btn-sm text-start" onclick="askQuickQuestion('How to conduct a search and seizure?')">
                            <i class="fas fa-home me-2"></i>Search & Seizure
                        </button>
                        <button class="btn btn-outline-danger btn-sm text-start" onclick="askQuickQuestion('What are the rights of an accused person?')">
                            <i class="fas fa-shield-alt me-2"></i>Accused Rights
                        </button>
                    </div>
                </div>
            </div>

            <!-- Usage Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Your AI Usage
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <h5 class="text-primary mb-1" id="questionsCount">0</h5>
                            <small class="text-muted">Questions Asked</small>
                        </div>
                        <div class="col-6 mb-2">
                            <h5 class="text-success mb-1" id="sectionsFound">0</h5>
                            <small class="text-muted">Sections Found</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Searches -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Searches
                    </h6>
                </div>
                <div class="card-body" id="recentSearches">
                    <p class="text-muted text-center mb-0">No recent searches</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">AI is analyzing your question...</p>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
}

.message {
    margin-bottom: 1rem;
}

.user-message {
    text-align: right;
}

.user-message .message-content {
    display: inline-block;
    max-width: 70%;
}

.user-message .bg-primary {
    background: linear-gradient(135deg, #0056b3, #007bff) !important;
}

.assistant-message .message-content {
    max-width: 85%;
}

.message-content .rounded-3 {
    word-wrap: break-word;
    line-height: 1.5;
}

.avatar {
    flex-shrink: 0;
}

#chatMessages {
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
}

#chatMessages::-webkit-scrollbar {
    width: 6px;
}

#chatMessages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#chatMessages::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

#chatMessages::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.legal-reference {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 0.5rem;
    margin: 0.5rem 0;
    border-radius: 0 4px 4px 0;
}

.section-number {
    font-weight: bold;
    color: #1976d2;
}

@media (max-width: 768px) {
    .card {
        height: auto !important;
    }
    
    #chatMessages {
        max-height: 300px !important;
    }
}
</style>

<script>
let questionCount = 0;
let totalSectionsFound = 0;
let recentSearches = [];

async function sendMessage(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('messageInput');
    const userMessage = messageInput.value.trim();
    
    if (!userMessage) return;
    
    // Add user message to chat
    addMessageToChat(userMessage, 'user');
    
    // Clear input and show loading
    messageInput.value = '';
    showLoadingModal();
    
    try {
        // Send to AI API
        const response = await fetch('api/ai_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                query: userMessage
            })
        });
        
        const data = await response.json();
        
        hideLoadingModal();
        
        if (data.ai_response && data.ai_response.success) {
            // Add AI response to chat
            addMessageToChat(data.ai_response.response, 'assistant', data.search_results);
            
            // Update stats
            questionCount++;
            totalSectionsFound += data.context_sections || 0;
            updateStats();
            
            // Add to recent searches
            addToRecentSearches(userMessage);
            
        } else {
            // Show error message
            addMessageToChat(
                'Sorry, I encountered an error: ' + (data.ai_response?.error || 'Unknown error'), 
                'assistant', 
                [], 
                true
            );
        }
        
    } catch (error) {
        hideLoadingModal();
        addMessageToChat('Sorry, I could not process your request. Please try again.', 'assistant', [], true);
        console.error('AI Chat Error:', error);
    }
}

function addMessageToChat(message, sender, searchResults = [], isError = false) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message mb-3`;
    
    const timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="d-flex align-items-start justify-content-end">
                <div class="message-content">
                    <div class="bg-primary text-white rounded-3 p-3">
                        <p class="mb-0">${escapeHtml(message)}</p>
                    </div>
                    <small class="text-muted d-block text-end mt-1">${timestamp}</small>
                </div>
                <div class="avatar bg-secondary text-white rounded-circle ms-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        `;
    } else {
        let legalReferences = '';
        if (searchResults && searchResults.length > 0) {
            legalReferences = '<div class="mt-3"><h6 class="text-primary mb-2">Referenced Legal Sections:</h6>';
            searchResults.forEach(result => {
                legalReferences += `
                    <div class="legal-reference mb-2">
                        <strong class="section-number">${result.database} - Section ${result.section_number}</strong><br>
                        <small>${result.section_name}</small>
                    </div>
                `;
            });
            legalReferences += '</div>';
        }
        
        const bgClass = isError ? 'bg-danger text-white' : 'bg-light';
        const iconClass = isError ? 'fa-exclamation-triangle' : 'fa-robot';
        
        messageDiv.innerHTML = `
            <div class="d-flex align-items-start">
                <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="message-content">
                    <div class="${bgClass} rounded-3 p-3">
                        <div>${formatAIResponse(message)}</div>
                        ${legalReferences}
                    </div>
                    <small class="text-muted">${timestamp}</small>
                </div>
            </div>
        `;
    }
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function formatAIResponse(response) {
    // Convert markdown-like formatting to HTML
    return response
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n/g, '<br>')
        .replace(/Section (\d+)/g, '<span class="section-number">Section $1</span>');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function askQuickQuestion(question) {
    document.getElementById('messageInput').value = question;
    sendMessage(new Event('submit'));
}

function showLoadingModal() {
    const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
    modal.show();
}

function hideLoadingModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
    if (modal) modal.hide();
}

function clearChat() {
    if (confirm('Are you sure you want to clear the chat history?')) {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.innerHTML = `
            <div class="message assistant-message mb-3">
                <div class="d-flex align-items-start">
                    <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <div class="bg-light rounded-3 p-3">
                            <p class="mb-0">Chat cleared. How can I help you with legal questions?</p>
                        </div>
                        <small class="text-muted">Just now</small>
                    </div>
                </div>
            </div>
        `;
    }
}

function exportChat() {
    const messages = document.querySelectorAll('.message');
    let chatText = 'AI Legal Assistant Chat Export\n';
    chatText += '=====================================\n\n';
    
    messages.forEach(message => {
        const isUser = message.classList.contains('user-message');
        const content = message.querySelector('.message-content div').textContent.trim();
        const sender = isUser ? 'User' : 'AI Assistant';
        chatText += `${sender}: ${content}\n\n`;
    });
    
    const blob = new Blob([chatText], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `ai_chat_${new Date().toISOString().split('T')[0]}.txt`;
    a.click();
    URL.revokeObjectURL(url);
}

function updateStats() {
    document.getElementById('questionsCount').textContent = questionCount;
    document.getElementById('sectionsFound').textContent = totalSectionsFound;
}

function addToRecentSearches(query) {
    recentSearches.unshift(query);
    if (recentSearches.length > 5) {
        recentSearches.pop();
    }
    
    const container = document.getElementById('recentSearches');
    if (recentSearches.length === 0) {
        container.innerHTML = '<p class="text-muted text-center mb-0">No recent searches</p>';
    } else {
        container.innerHTML = recentSearches.map(search => 
            `<div class="recent-search mb-2">
                <button class="btn btn-sm btn-outline-secondary w-100 text-start" onclick="askQuickQuestion('${escapeHtml(search)}')">
                    <i class="fas fa-history me-2"></i>
                    ${escapeHtml(search.substring(0, 40))}${search.length > 40 ? '...' : ''}
                </button>
            </div>`
        ).join('');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('messageInput').focus();
});

// Enter key handling
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage(e);
    }
});
</script>

<?php include 'includes/footer.php'; ?>