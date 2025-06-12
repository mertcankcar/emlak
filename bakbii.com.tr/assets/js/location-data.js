// Function to fetch and parse JSON files
async function fetchJsonData(filePath) {
    try {
        const response = await fetch(filePath);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error(`Error loading data from ${filePath}:`, error);
        return [];
    }
}

// Load provinces from sehirler.json
async function loadProvinces() {
    const provinces = await fetchJsonData('sehirler.json');
    return provinces.map(province => ({
        id: province.sehir_id,
        name: province.sehir_adi
    }));
}

import { istanbulDistricts } from './istanbul-districts.js';

// Load districts from ilceler.json or use predefined Istanbul districts
async function loadDistricts(provinceId) {
    // If Istanbul is selected (provinceId === "34"), return predefined districts
    if (provinceId === "34") {
        return istanbulDistricts;
    }
    
    // For other provinces, load from ilceler.json
    const districts = await fetchJsonData('ilceler.json');
    return districts
        .filter(district => district.il_id === provinceId)
        .map(district => ({
            id: district.ilce_id,
            name: district.ilce_adi
        }));
}

// Load neighborhoods from mahalleler-[1-4].json files
async function loadNeighborhoods(districtId) {
    let neighborhoods = [];
    
    // Try loading from each mahalleler file
    for (let i = 1; i <= 4; i++) {
        const fileData = await fetchJsonData(`mahalleler-${i}.json`);
        const matchingNeighborhoods = fileData
            .filter(neighborhood => neighborhood.ilce_id === districtId)
            .map(neighborhood => ({
                id: neighborhood.mahalle_id,
                name: neighborhood.mahalle_adi,
                type: neighborhood.mahalle_tip // Assuming this exists in the JSON
            }));
        
        neighborhoods = neighborhoods.concat(matchingNeighborhoods);
    }
    
    return neighborhoods;
}

// Export the functions
export { loadProvinces, loadDistricts, loadNeighborhoods };