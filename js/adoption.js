// Adoption Page JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Filter functionality
    const filterTags = document.querySelectorAll('.filter-tag');
    const petCards = document.querySelectorAll('.pet-card');

    filterTags.forEach(tag => {
        tag.addEventListener('click', () => {
            // Update active state
            filterTags.forEach(t => t.classList.remove('active'));
            tag.classList.add('active');

            // Get filter type
            const filterType = tag.getAttribute('data-filter');

            // Filter pets
            petCards.forEach(card => {
                const petType = card.getAttribute('data-type');

                if (filterType === 'all' || petType === filterType) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.5s';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Search functionality
    const searchInput = document.getElementById('pet-search');
    const searchBtn = document.querySelector('.search-btn');

    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase();

        petCards.forEach(card => {
            const petName = card.querySelector('h3').textContent.toLowerCase();
            const petMeta = card.querySelector('.pet-meta').textContent.toLowerCase();
            const petTraits = Array.from(card.querySelectorAll('.trait'))
                .map(t => t.textContent.toLowerCase())
                .join(' ');

            const matchesSearch = petName.includes(searchTerm) ||
                petMeta.includes(searchTerm) ||
                petTraits.includes(searchTerm);

            card.style.display = matchesSearch ? 'block' : 'none';
        });
    }

    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keyup', (e) => {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    // Favorite functionality
    const favoriteBtns = document.querySelectorAll('.favorite-btn');

    favoriteBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            btn.classList.toggle('active');

            const icon = btn.querySelector('i');
            if (btn.classList.contains('active')) {
                icon.classList.remove('fa-regular');
                icon.classList.add('fa-solid');
            } else {
                icon.classList.remove('fa-solid');
                icon.classList.add('fa-regular');
            }
        });
    });

    // Pet card click - navigate to detail (placeholder)
    petCards.forEach(card => {
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.favorite-btn') && !e.target.closest('.adopt-btn')) {
                const petName = card.querySelector('h3').textContent;
                console.log(`Clicked on ${petName}`);
                // TODO: Navigate to pet detail page
            }
        });
    });
});

// Add fadeIn animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
`;
document.head.appendChild(style);
