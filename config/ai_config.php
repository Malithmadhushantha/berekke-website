<?php
// AI Configuration for Berekke Website

// Google Gemini API Configuration
define('GEMINI_API_KEY', 'AIzaSyBd30GWFLTDZ20nnPitZ6hY1NzT4aYqWuU'); // Replace with your actual API key
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');

// Alternative AI APIs (if you want to use them)
define('OPENAI_API_KEY', 'AIzaSyBd30GWFLTDZ20nnPitZ6hY1NzT4aYqWuU'); // Optional: OpenAI API key
define('HUGGINGFACE_API_KEY', ''); // Optional: Hugging Face API key

// AI Chat Settings
define('MAX_CONTEXT_LENGTH', 8000); // Maximum context length for conversations
define('MAX_SEARCH_RESULTS', 5); // Maximum number of legal sections to include in context
define('AI_TEMPERATURE', 0.7); // AI response creativity (0.0 = deterministic, 1.0 = creative)

// Legal Database Search Configuration
define('ENABLE_FUZZY_SEARCH', true); // Enable fuzzy matching for legal searches
define('MIN_SEARCH_RELEVANCE', 0.3); // Minimum relevance score for search results

/**
 * Make API call to Gemini
 */
function callGeminiAPI($prompt, $systemPrompt = '') {
    $apiKey = GEMINI_API_KEY;
    
    if (empty($apiKey) || $apiKey === 'AIzaSyBd30GWFLTDZ20nnPitZ6hY1NzT4aYqWuU') {
        return [
            'error' => 'Gemini API key not configured. Please set your API key in config/ai_config.php'
        ];
    }
    
    $url = GEMINI_API_URL . '?key=' . $apiKey;
    
    // Combine system prompt with user prompt
    $fullPrompt = '';
    if (!empty($systemPrompt)) {
        $fullPrompt = $systemPrompt . "\n\nUser Question: " . $prompt;
    } else {
        $fullPrompt = $prompt;
    }
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $fullPrompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => AI_TEMPERATURE,
            'topP' => 0.95,
            'topK' => 64,
            'maxOutputTokens' => 8192,
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return ['error' => 'Failed to connect to AI service'];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode !== 200) {
        return [
            'error' => 'AI API Error: ' . ($responseData['error']['message'] ?? 'Unknown error'),
            'details' => $responseData
        ];
    }
    
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        return [
            'success' => true,
            'response' => $responseData['candidates'][0]['content']['parts'][0]['text']
        ];
    }
    
    return ['error' => 'Unexpected API response format'];
}

/**
 * Search legal databases for relevant content
 */
function searchLegalDatabases($query, $pdo) {
    $searchResults = [];
    
    // Search in all three legal databases
    $databases = [
        'penal_code' => 'Penal Code',
        'criminal_procedure_code' => 'Criminal Procedure Code', 
        'evidence_ordinance' => 'Evidence Ordinance'
    ];
    
    foreach ($databases as $table => $name) {
        $sql = "SELECT id, section_number, section_name, section_topic, section_text, 
                       explanation_1, illustrations_1 
                FROM {$table} 
                WHERE section_number LIKE :query 
                   OR section_name LIKE :query 
                   OR section_topic LIKE :query 
                   OR section_text LIKE :query
                ORDER BY 
                    CASE 
                        WHEN section_number LIKE :exact_query THEN 1
                        WHEN section_name LIKE :query THEN 2
                        WHEN section_topic LIKE :query THEN 3
                        ELSE 4
                    END
                LIMIT " . MAX_SEARCH_RESULTS;
        
        $stmt = $pdo->prepare($sql);
        $searchParam = '%' . $query . '%';
        $exactParam = $query;
        
        $stmt->bindParam(':query', $searchParam);
        $stmt->bindParam(':exact_query', $exactParam);
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        
        foreach ($results as $result) {
            $result['database'] = $name;
            $result['table'] = $table;
            $searchResults[] = $result;
        }
    }
    
    return array_slice($searchResults, 0, MAX_SEARCH_RESULTS);
}

/**
 * Create system prompt with legal context
 */
function createLegalSystemPrompt($searchResults, $userQuery) {
    $systemPrompt = "You are a specialized AI assistant for Sri Lankan Police officers. You have access to the complete Sri Lankan legal database including Penal Code, Criminal Procedure Code, and Evidence Ordinance.

INSTRUCTIONS:
1. Analyze the provided legal sections carefully
2. Provide accurate, helpful responses based on Sri Lankan law
3. Reference specific section numbers when applicable
4. Explain legal concepts clearly for police officers
5. If asked about procedures, provide step-by-step guidance
6. Always emphasize the importance of following proper legal procedures
7. If you cannot find relevant information in the provided context, say so clearly

AVAILABLE LEGAL CONTEXT:\n";

    if (empty($searchResults)) {
        $systemPrompt .= "No specific legal sections found for this query. Provide general guidance based on Sri Lankan law principles.\n";
    } else {
        foreach ($searchResults as $result) {
            $systemPrompt .= "\n--- {$result['database']} - Section {$result['section_number']} ---\n";
            $systemPrompt .= "Title: {$result['section_name']}\n";
            if (!empty($result['section_topic'])) {
                $systemPrompt .= "Topic: {$result['section_topic']}\n";
            }
            $systemPrompt .= "Content: {$result['section_text']}\n";
            if (!empty($result['explanation_1'])) {
                $systemPrompt .= "Explanation: {$result['explanation_1']}\n";
            }
            if (!empty($result['illustrations_1'])) {
                $systemPrompt .= "Illustrations: {$result['illustrations_1']}\n";
            }
        }
    }
    
    $systemPrompt .= "\nUser Query: {$userQuery}\n";
    $systemPrompt .= "\nPlease provide a comprehensive answer in English or Sinhala (match the user's language preference). Include relevant section numbers and practical guidance for police officers.";
    
    return $systemPrompt;
}

/**
 * Main AI chat function
 */
function processLegalAIQuery($userQuery, $pdo) {
    // Search for relevant legal content
    $searchResults = searchLegalDatabases($userQuery, $pdo);
    
    // Create system prompt with legal context
    $systemPrompt = createLegalSystemPrompt($searchResults, $userQuery);
    
    // Call AI API
    $aiResponse = callGeminiAPI($userQuery, $systemPrompt);
    
    // Return structured response
    return [
        'user_query' => $userQuery,
        'search_results' => $searchResults,
        'ai_response' => $aiResponse,
        'context_sections' => count($searchResults)
    ];
}

/**
 * Log AI interactions for analysis
 */
function logAIInteraction($userId, $query, $response, $pdo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO ai_chat_logs (user_id, query, response, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $query, json_encode($response)]);
    } catch (Exception $e) {
        // Silent fail for logging
        error_log("Failed to log AI interaction: " . $e->getMessage());
    }
}
?>