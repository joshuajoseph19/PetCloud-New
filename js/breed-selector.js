/**
 * PetCloud - Breed Selector Component
 * A searchable dropdown for breed selection with dynamic filtering
 */

class BreedSelector {
    constructor(options = {}) {
        this.containerId = options.containerId || 'breed-selector-container';
        this.petTypeSelectId = options.petTypeSelectId || 'pet-type-select';
        this.onBreedSelect = options.onBreedSelect || null;
        this.placeholder = options.placeholder || 'Search for a breed...';
        this.allowUnknown = options.allowUnknown !== false; // Default true

        this.container = document.getElementById(this.containerId);
        this.petTypeSelect = document.getElementById(this.petTypeSelectId);

        this.selectedBreedId = null;
        this.selectedBreedName = null;
        this.breedsData = [];
        this.filteredBreeds = [];

        this.init();
    }

    init() {
        if (!this.container) {
            console.error(`Container with ID "${this.containerId}" not found`);
            return;
        }

        this.render();
        this.attachEventListeners();

        // Listen for pet type changes
        if (this.petTypeSelect) {
            this.petTypeSelect.addEventListener('change', () => {
                this.loadBreeds();
            });
        }
    }

    render() {
        this.container.innerHTML = `
            <div class="breed-selector">
                <label for="breed-search-input" class="breed-label">
                    Breed <span class="optional-tag">(Optional)</span>
                </label>
                <div class="breed-search-wrapper">
                    <input 
                        type="text" 
                        id="breed-search-input" 
                        class="breed-search-input"
                        placeholder="${this.placeholder}"
                        autocomplete="off"
                        readonly
                    />
                    <i class="fas fa-chevron-down breed-dropdown-icon"></i>
                    <div class="breed-dropdown" id="breed-dropdown" style="display: none;">
                        <div class="breed-dropdown-search">
                            <input 
                                type="text" 
                                id="breed-filter-input" 
                                class="breed-filter-input"
                                placeholder="Type to search..."
                                autocomplete="off"
                            />
                            <i class="fas fa-search breed-search-icon"></i>
                        </div>
                        <div class="breed-dropdown-content" id="breed-dropdown-content">
                            <div class="breed-loading">
                                <i class="fas fa-spinner fa-spin"></i> Loading breeds...
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="breed-id-hidden" name="breed_id" value="" />
                <small class="breed-help-text">Select a breed or leave blank if unknown</small>
            </div>
        `;
    }

    attachEventListeners() {
        const searchInput = document.getElementById('breed-search-input');
        const dropdown = document.getElementById('breed-dropdown');
        const filterInput = document.getElementById('breed-filter-input');

        // Open dropdown on click
        searchInput.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown(true);
        });

        // Filter breeds as user types
        filterInput.addEventListener('input', (e) => {
            this.filterBreeds(e.target.value);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.toggleDropdown(false);
            }
        });
    }

    toggleDropdown(show) {
        const dropdown = document.getElementById('breed-dropdown');
        const searchInput = document.getElementById('breed-search-input');
        const filterInput = document.getElementById('breed-filter-input');

        if (show) {
            dropdown.style.display = 'block';
            searchInput.classList.add('active');
            filterInput.focus();

            // Load breeds if not already loaded
            if (this.breedsData.length === 0) {
                this.loadBreeds();
            }
        } else {
            dropdown.style.display = 'none';
            searchInput.classList.remove('active');
            filterInput.value = '';
        }
    }

    async loadBreeds() {
        const petTypeId = this.petTypeSelect ? this.petTypeSelect.value : null;

        if (!petTypeId) {
            this.showMessage('Please select a pet type first', 'info');
            return;
        }

        const dropdownContent = document.getElementById('breed-dropdown-content');
        dropdownContent.innerHTML = '<div class="breed-loading"><i class="fas fa-spinner fa-spin"></i> Loading breeds...</div>';

        try {
            const response = await fetch(`api/get_breeds.php?pet_type_id=${petTypeId}`);
            const data = await response.json();

            if (data.success) {
                this.breedsData = data.data;
                this.renderBreeds();
            } else {
                this.showMessage(data.error || 'Failed to load breeds', 'error');
            }
        } catch (error) {
            console.error('Error loading breeds:', error);
            this.showMessage('Failed to load breeds. Please try again.', 'error');
        }
    }

    renderBreeds(searchTerm = '') {
        const dropdownContent = document.getElementById('breed-dropdown-content');

        if (this.breedsData.length === 0) {
            dropdownContent.innerHTML = '<div class="breed-empty">No breeds available</div>';
            return;
        }

        let html = '';

        this.breedsData.forEach(group => {
            // Filter breeds within group
            const filteredBreeds = group.breeds.filter(breed =>
                breed.name.toLowerCase().includes(searchTerm.toLowerCase())
            );

            if (filteredBreeds.length > 0) {
                html += `<div class="breed-group">`;
                html += `<div class="breed-group-header">${group.group_name}</div>`;

                filteredBreeds.forEach(breed => {
                    const isSelected = this.selectedBreedId === breed.id;
                    html += `
                        <div class="breed-option ${isSelected ? 'selected' : ''}" 
                             data-breed-id="${breed.id}" 
                             data-breed-name="${breed.name}"
                             data-group-name="${group.group_name}">
                            <span class="breed-name">${breed.name}</span>
                            ${breed.description ? `<span class="breed-description">${breed.description}</span>` : ''}
                            ${isSelected ? '<i class="fas fa-check breed-check-icon"></i>' : ''}
                        </div>
                    `;
                });

                html += `</div>`;
            }
        });

        if (html === '') {
            html = '<div class="breed-empty">No breeds match your search</div>';
        }

        dropdownContent.innerHTML = html;

        // Attach click handlers to breed options
        const breedOptions = dropdownContent.querySelectorAll('.breed-option');
        breedOptions.forEach(option => {
            option.addEventListener('click', () => {
                this.selectBreed(
                    parseInt(option.dataset.breedId),
                    option.dataset.breedName,
                    option.dataset.groupName
                );
            });
        });
    }

    filterBreeds(searchTerm) {
        this.renderBreeds(searchTerm);
    }

    selectBreed(breedId, breedName, groupName) {
        this.selectedBreedId = breedId;
        this.selectedBreedName = breedName;

        // Update UI
        const searchInput = document.getElementById('breed-search-input');
        const hiddenInput = document.getElementById('breed-id-hidden');

        searchInput.value = `${breedName} (${groupName})`;
        hiddenInput.value = breedId;

        // Close dropdown
        this.toggleDropdown(false);

        // Trigger callback
        if (this.onBreedSelect && typeof this.onBreedSelect === 'function') {
            this.onBreedSelect({
                id: breedId,
                name: breedName,
                group: groupName
            });
        }

        // Re-render to show selection
        this.renderBreeds();
    }

    clearSelection() {
        this.selectedBreedId = null;
        this.selectedBreedName = null;

        const searchInput = document.getElementById('breed-search-input');
        const hiddenInput = document.getElementById('breed-id-hidden');

        searchInput.value = '';
        hiddenInput.value = '';

        this.renderBreeds();
    }

    showMessage(message, type = 'info') {
        const dropdownContent = document.getElementById('breed-dropdown-content');
        const iconClass = type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
        dropdownContent.innerHTML = `
            <div class="breed-message breed-message-${type}">
                <i class="fas ${iconClass}"></i> ${message}
            </div>
        `;
    }

    getValue() {
        return {
            id: this.selectedBreedId,
            name: this.selectedBreedName
        };
    }

    setValue(breedId, breedName, groupName) {
        this.selectBreed(breedId, breedName, groupName);
    }
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BreedSelector;
}
