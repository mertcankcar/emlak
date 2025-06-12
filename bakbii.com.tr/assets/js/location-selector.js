import { loadProvinces, loadDistricts, loadNeighborhoods } from './location-data.js';

document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('province');
    const districtSelect = document.getElementById('district');
    const neighborhoodSelect = document.getElementById('neighborhood');
    
    // Initially disable district and neighborhood selects
    districtSelect.disabled = true;
    neighborhoodSelect.disabled = true;
    
    // Load provinces on page load
    loadProvinces().then(provinces => {
        let options = '<option value="">İl seçiniz</option>';
        provinces.forEach(province => {
            options += `<option value="${province.id}">${province.name}</option>`;
        });
        provinceSelect.innerHTML = options;
    }).catch(error => console.error('Error loading provinces:', error));

    // Event listener for province selection
    provinceSelect.addEventListener('change', function() {
        if (this.value) {
            loadDistricts(this.value).then(districts => {
                let options = '<option value="">İlçe seçiniz</option>';
                districts.forEach(district => {
                    options += `<option value="${district.id}">${district.name}</option>`;
                });
                districtSelect.innerHTML = options;
                districtSelect.disabled = false;
            }).catch(error => console.error('Error loading districts:', error));
        } else {
            districtSelect.disabled = true;
            neighborhoodSelect.disabled = true;
            districtSelect.innerHTML = '<option value="">İlçe seçiniz</option>';
            neighborhoodSelect.innerHTML = '<option value="">Mahalle/Köy seçiniz</option>';
        }
    });

    // Event listener for district selection
    districtSelect.addEventListener('change', function() {
        if (this.value) {
            loadNeighborhoods(this.value).then(neighborhoods => {
                let options = '<option value="">Mahalle/Köy seçiniz</option>';
                neighborhoods.forEach(neighborhood => {
                    const type = neighborhood.type === 'village' ? 'Köy' : 'Mahalle';
                    options += `<option value="${neighborhood.id}">${neighborhood.name} (${type})</option>`;
                });
                neighborhoodSelect.innerHTML = options;
                neighborhoodSelect.disabled = false;
            }).catch(error => console.error('Error loading neighborhoods:', error));
        } else {
            neighborhoodSelect.disabled = true;
            neighborhoodSelect.innerHTML = '<option value="">Mahalle/Köy seçiniz</option>';
        }
    });
});
