 

// Tự động focus vào ô tìm kiếm
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        // Nếu có từ khóa tìm kiếm, highlight nó
        if (searchInput.value) {
            searchInput.select();
        }
        
        // Xử lý phím Enter để submit form
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }
    
    // Hiển thị xóa tìm kiếm khi có nội dung
    const updateClearButton = () => {
        const clearBtn = document.querySelector('.clear-search');
        if (clearBtn) {
            clearBtn.style.display = searchInput && searchInput.value ? 'inline-block' : 'none';
        }
    };
    
    if (searchInput) {
        searchInput.addEventListener('input', updateClearButton);
        updateClearButton();
    }
});

