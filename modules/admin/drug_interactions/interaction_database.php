<?php
/**
 * Common Drug Interactions Database
 * This file contains common drug interactions that can be imported into the system
 */

// Common drug interactions data
$common_interactions = [
    [
        'med_1' => 'Aspirin',
        'med_2' => 'Ibuprofen',
        'severity' => 'High',
        'description' => 'Both are NSAIDs. Combined use increases risk of gastrointestinal bleeding and ulcers.',
        'recommendation' => 'Avoid concurrent use. If pain relief needed, use only one NSAID.'
    ],
    [
        'med_1' => 'Warfarin',
        'med_2' => 'Aspirin',
        'severity' => 'Critical',
        'description' => 'Aspirin increases anticoagulant effect of Warfarin, significantly increasing bleeding risk.',
        'recommendation' => 'Avoid combination. Use alternative pain reliever like acetaminophen.'
    ],
    [
        'med_1' => 'Metformin',
        'med_2' => 'Contrast Dye',
        'severity' => 'High',
        'description' => 'Contrast dye can impair kidney function, increasing risk of lactic acidosis with Metformin.',
        'recommendation' => 'Hold Metformin 48 hours before and after contrast procedures.'
    ],
    [
        'med_1' => 'ACE Inhibitors',
        'med_2' => 'Potassium Supplements',
        'severity' => 'High',
        'description' => 'Both increase potassium levels, risk of hyperkalemia.',
        'recommendation' => 'Monitor potassium levels regularly. May need dose adjustment.'
    ],
    [
        'med_1' => 'Statins',
        'med_2' => 'Fibrates',
        'severity' => 'Moderate',
        'description' => 'Combined use increases risk of muscle pain and rhabdomyolysis.',
        'recommendation' => 'Monitor for muscle pain. Regular CK level monitoring recommended.'
    ],
    [
        'med_1' => 'Digoxin',
        'med_2' => 'Verapamil',
        'severity' => 'High',
        'description' => 'Verapamil increases digoxin levels, risk of toxicity.',
        'recommendation' => 'Monitor digoxin levels. May need dose reduction.'
    ],
    [
        'med_1' => 'Lithium',
        'med_2' => 'NSAIDs',
        'severity' => 'High',
        'description' => 'NSAIDs reduce lithium clearance, increasing toxicity risk.',
        'recommendation' => 'Avoid NSAIDs. Use acetaminophen for pain relief.'
    ],
    [
        'med_1' => 'Theophylline',
        'med_2' => 'Ciprofloxacin',
        'severity' => 'Moderate',
        'description' => 'Ciprofloxacin increases theophylline levels.',
        'recommendation' => 'Monitor theophylline levels. May need dose adjustment.'
    ],
    [
        'med_1' => 'Clopidogrel',
        'med_2' => 'Omeprazole',
        'severity' => 'High',
        'description' => 'Omeprazole reduces effectiveness of Clopidogrel.',
        'recommendation' => 'Use alternative PPI like pantoprazole or H2 blocker.'
    ],
    [
        'med_1' => 'Methotrexate',
        'med_2' => 'NSAIDs',
        'severity' => 'High',
        'description' => 'NSAIDs reduce methotrexate clearance, increasing toxicity.',
        'recommendation' => 'Avoid NSAIDs. Monitor renal function and methotrexate levels.'
    ]
];

/**
 * Get common interactions
 */
function get_common_interactions() {
    global $common_interactions;
    return $common_interactions;
}

/**
 * Import common interactions into database
 */
function import_common_interactions($conn) {
    $imported = 0;
    $skipped = 0;
    
    foreach (get_common_interactions() as $interaction) {
        // Get medicine IDs
        $med_1_result = $conn->query("SELECT Med_ID FROM meds WHERE Med_Name = '" . $conn->real_escape_string($interaction['med_1']) . "' LIMIT 1");
        $med_2_result = $conn->query("SELECT Med_ID FROM meds WHERE Med_Name = '" . $conn->real_escape_string($interaction['med_2']) . "' LIMIT 1");
        
        if ($med_1_result && $med_1_result->num_rows > 0 && $med_2_result && $med_2_result->num_rows > 0) {
            $med_1 = $med_1_result->fetch_assoc()['Med_ID'];
            $med_2 = $med_2_result->fetch_assoc()['Med_ID'];
            
            // Check if interaction already exists
            $check = $conn->query("SELECT interaction_id FROM drug_interactions WHERE (med_id_1 = $med_1 AND med_id_2 = $med_2) OR (med_id_1 = $med_2 AND med_id_2 = $med_1)");
            
            if ($check && $check->num_rows === 0) {
                $severity = $conn->real_escape_string($interaction['severity']);
                $description = $conn->real_escape_string($interaction['description']);
                $recommendation = $conn->real_escape_string($interaction['recommendation']);
                
                $sql = "INSERT INTO drug_interactions (med_id_1, med_id_2, severity, description, recommendation) 
                        VALUES ($med_1, $med_2, '$severity', '$description', '$recommendation')";
                
                if ($conn->query($sql)) {
                    $imported++;
                } else {
                    $skipped++;
                }
            } else {
                $skipped++;
            }
        } else {
            $skipped++;
        }
    }
    
    return ['imported' => $imported, 'skipped' => $skipped];
}
?>
