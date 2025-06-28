// Function to show the result with the fixed buttons in a <p> element
function showFixedResult() {
    if (resultBox) {
        resultBox.style.display = 'block';
        resultBox.style.textAlign = 'center';
        resultBox.style.padding = '20px';
        resultBox.style.color = '#2c3e50';
        resultBox.style.position = 'relative';
        
        // Create result content with proper font sizes and weights
        let resultHTML = `
            <div style="margin: 0 auto 30px; max-width: 600px; font-size: 16px; line-height: 1.5; text-align: center;">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">¡Tu resultado está listo!</h3>
                <div style="font-size: 24px; margin-bottom: 2px;">Tu ruta ideal es:</div>
                <div style="font-size: 28px; font-weight: bold; color: #2c3e50; margin-bottom: 30px;">
                    ${topRoutes[0]?.route || 'No disponible'}
                </div>
        `;
        
        // Add secondary route if available
        if (topRoutes.length > 1) {
            resultHTML += `
                <div style="margin: 10px 0 5px;">
                    <div style="margin-bottom: 2px; font-size: 20px;">También podrías explorar:</div>
                    <div style="font-weight: bold; font-size: 22px; color: #2c3e50;">
                        ${topRoutes[1]?.route || ''}
                    </div>
                </div>
            `;
        }
        
        resultHTML += `</div>`;
        
        // Add buttons with custom text and links in a <p> element instead of a <div>
        resultHTML += `
            <p style="display: flex; flex-direction: column; gap: 15px; max-width: 400px; margin: 0 auto 30px;">
                <a href="https://www.coursera.org/" target="_blank" rel="noopener noreferrer" 
                   style="display: block; padding: 12px 20px; background-color: #0056D2; 
                          color: white; text-decoration: none; border-radius: 5px;
                          font-size: 16px; font-weight: normal; transition: background-color 0.3s; margin-bottom: 10px;">
                    Si ya sos parte del programa y tenés tu licencia activa, ingresá a Coursera acá
                </a>
                <a href="https://vinculo.com.py/jovenes-conectados/#rutas-de-aprendizaje" target="_blank" rel="noopener noreferrer" 
                   style="display: block; padding: 12px 20px; background-color: #0056D2; 
                          color: white; text-decoration: none; border-radius: 5px;
                          font-size: 16px; font-weight: normal; transition: background-color 0.3s;">
                    Explorá las rutas de aprendizaje y los cursos incluidos en cada ruta antes de empezar
                </a>
            </p>
        `;
        
        resultHTML += `
            <button id="restart-btn" style="margin-top: 20px; padding: 10px 20px; 
                    font-size: 16px; cursor: pointer; background-color: #808080; 
                    color: white; border: none; border-radius: 5px; transition: background-color 0.3s;">
                Volver a tomar el test
            </button>
        `;
        
        resultBox.innerHTML = resultHTML;
        
        // Completely reset the app when restart button is clicked
        const restartBtn = document.getElementById('restart-btn');
        if (restartBtn) {
            // Remove any existing listeners
            const newRestartBtn = restartBtn.cloneNode(true);
            restartBtn.parentNode.replaceChild(newRestartBtn, restartBtn);
            
            // Use the restartTest function for consistent restart behavior
            newRestartBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                return restartTest();
            };
            
            console.log('Restart button ready');
        }
    }
}

// Function to improve persistence of results
function improveResultPersistence() {
    // Check for saved result first
    const savedResult = getSavedResult();
    if (savedResult) {
        console.log('Found saved result, showing it');
        showSavedResult(savedResult);
        return true;
    }
    return false;
}

// Function to fix state persistence issues
function fixStatePersistence() {
    // Add this to the checkSavedResults function after the restart flag handling
    if (restartFlag === 'true') {
        // ... existing code ...
        return; // Exit early after restart
    }
    
    // Check for saved result first before checking for test state
    if (improveResultPersistence()) {
        return; // Exit if we found and showed a saved result
    }
    
    // Continue with normal test state restoration
    // ... existing code ...
}
