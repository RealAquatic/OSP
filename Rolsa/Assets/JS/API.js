async function getEVChargers(city) {
    if (!city || city.trim() === '') {
        return [];
    }
    
    try {
        const url = "Assets/PHP/getEVChargers.php?city=" + encodeURIComponent(city);
        
        const response = await fetch(url, {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        });
        
        if (!response.ok) {
            throw new Error(`Server Error: ${response.status}`);
        }
        
        const data = await response.json();
        console.log("Fetched EV Chargers Data:", data);
        if (data.error) {
            console.error("API Error:", data.error);
            return [];
        }
        
        return Array.isArray(data) ? data : [];
    } catch (error) {
        console.error("Error fetching EV chargers:", error);
        return [];
    }
}

function formatChargerInfo(charger) {
    console.log(charger);

    const connections = charger.connections || [];

    const totalConnectors = connections.reduce((sum, c) => sum + (c.num_connectors || 0), 0);

    const level2 = connections
        .filter(c => c.level === 2)
        .reduce((sum, c) => sum + (c.num_connectors || 0), 0);

    const dcFast = connections
        .filter(c => c.level === 3)
        .reduce((sum, c) => sum + (c.num_connectors || 0), 0);

    return {
        name: charger.name || "Unknown Station",
        address: charger.address || charger.city || "Address not available",
        city: charger.city || "",
        region: charger.region || "",
        country: charger.country || "",
        isActive: charger.is_active || false,

        chargers: totalConnectors,
        level2Chargers: level2,
        dcChargers: dcFast,
        networks: connections.map(c => c.type_name) || [],

        latitude: charger.latitude || null,
        longitude: charger.longitude || null
    };
}

function displayChargerResults(chargers, containerId = "EVResultsContainer") {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    if (!chargers || chargers.length === 0) {
        container.innerHTML = '<div class="EVNoResults">No charging stations found in this location. Try another city.</div>';
        return;
    }
    
    container.innerHTML = chargers.map(charger => createChargerCard(charger)).join("");
}

function createChargerCard(charger) {
    const info = formatChargerInfo(charger);
    
    return `
        <div class="EVVehicleCard">
            <div class="EVVehicleHeader">
                <h3 class="EVVehicleTitle">${info.name}</h3>
                <p class="EVVehicleSubtitle">${info.address}</p>
            </div>
            
            <div class="EVSpecSection">
                <h4 class="EVSpecSectionTitle">Charger Information</h4>
                <div class="EVSpec">
                    <span class="EVSpecLabel">Total Chargers:</span>
                    <span class="EVSpecValue">${info.chargers}</span>
                </div>
                <div class="EVSpec">
                    <span class="EVSpecLabel">Level 2 Chargers:</span>
                    <span class="EVSpecValue">${info.level2Chargers}</span>
                </div>
                <div class="EVSpec">
                    <span class="EVSpecLabel">DC Fast Chargers:</span>
                    <span class="EVSpecValue">${info.dcChargers}</span>
                </div>
                <div class="EVSpec">
                    <span class="EVSpecLabel">Network:</span>
                    <span class="EVSpecValue">${info.networks}</span>
                </div>
            </div>
            
            ${info.latitude && info.longitude ? `
            <div class="EVChargerLocation">
                <p class="EVCoordinates">📍 ${info.latitude.toFixed(4)}, ${info.longitude.toFixed(4)}</p>
            </div>
            ` : ''}
        </div>
    `;
}

function initEVChargerSearch() {
    const citySelect = document.getElementById("EVCitySelect");
    const searchBtn = document.getElementById("EVSearchBtn");
    const loadingIndicator = document.getElementById("EVLoadingIndicator");
    const resultsContainer = document.getElementById("EVResultsContainer");
    
    if (!searchBtn || !citySelect) return;
    
    async function performSearch() {
        const city = citySelect.value;
        
        if (!city || city.trim() === '') {
            resultsContainer.innerHTML = '<div class="EVNoResults">Please select a city to search for charging stations.</div>';
            return;
        }
        
        loadingIndicator.style.display = "block";
        resultsContainer.innerHTML = "";
        
        try {
            const chargers = await getEVChargers(city);
            displayChargerResults(chargers);
        } catch (error) {
            console.error("Search error:", error);
            resultsContainer.innerHTML = '<div class="EVNoResults">Error loading chargers. Please try again.</div>';
        } finally {
            loadingIndicator.style.display = "none";
        }
    }
    
    searchBtn.addEventListener("click", performSearch);
    
    citySelect.addEventListener("change", (e) => {
        if (e.target.value) {
            performSearch();
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initEVChargerSearch();
});