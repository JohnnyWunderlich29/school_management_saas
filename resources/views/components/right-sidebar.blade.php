<!-- Right Sidebar -->
<div id="rightSidebar" class="fixed top-0 right-0 h-full w-64 bg-indigo-700 text-white shadow-lg transform translate-x-full transition-all duration-500 ease-out z-[9999]" style="transform: translateX(100%); visibility: hidden;">
    <div class="p-6">
        <div class="flex justify-end items-center">
            <button id="closeRightSidebar" class="text-white hover:text-indigo-200 transition-colors">
                ×
            </button>
        </div>
    </div>
    <nav class="mt-6">
        <div class="px-4 py-2">
            <a href="/historico" class="flex items-center px-4 py-3 text-white hover:bg-indigo-600 rounded-lg transition-colors duration-200">
                <i class="fas fa-history mr-3"></i>
                <span>Histórico</span>
            </a>
        </div>
    </nav>
</div>

<!-- Toggle Button -->
<div id="rightSidebarToggle" class="fixed bg-indigo-600 hover:bg-indigo-700 text-white p-3 shadow-lg cursor-pointer transition-all duration-500 ease-out z-50" style="top: 50%; right: 0; transform: translateY(-50%) translateX(25%); border-radius: 10px 0px 0px 10px;">
    <i class="fas fa-chevron-left text-lg"></i>
</div>





<script>
// Right sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const rightSidebar = document.getElementById('rightSidebar');
    const closeRightSidebar = document.getElementById('closeRightSidebar');
    const rightSidebarOverlay = document.getElementById('rightSidebarOverlay');
    const rightSidebarToggle = document.getElementById('rightSidebarToggle');
    const toggleIcon = rightSidebarToggle?.querySelector('i');
    
    function openRightSidebar() {
        console.log('Opening sidebar - before:', rightSidebar.className);
        rightSidebar.style.visibility = 'visible';
        rightSidebar.classList.remove('translate-x-full');
        rightSidebar.style.transform = 'translateX(0)';
        console.log('Opening sidebar - after:', rightSidebar.className);
        if (toggleIcon) {
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');
        }
        rightSidebarToggle.style.right = '256px';
        rightSidebarToggle.style.transform = 'translateY(-50%)';
    }
    
    function closeRightSidebarFunc() {
        console.log('Closing sidebar - before:', rightSidebar.className);
        rightSidebar.classList.add('translate-x-full');
        rightSidebar.style.transform = 'translateX(100%)';
        rightSidebar.style.visibility = 'hidden';
        console.log('Closing sidebar - after:', rightSidebar.className);
        if (toggleIcon) {
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-chevron-left');
        }
        rightSidebarToggle.style.right = '0';
        rightSidebarToggle.style.transform = 'translateY(-50%) translateX(25%)';
    }
    
    // Toggle button click with higher priority
    rightSidebarToggle?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        console.log('Toggle button clicked');
        console.log('Current classes:', rightSidebar.className);
        console.log('Has translate-x-full:', rightSidebar.classList.contains('translate-x-full'));
        
        // Force check the current state
        const isHidden = rightSidebar.classList.contains('translate-x-full');
        
        if (isHidden) {
            console.log('Opening sidebar');
            openRightSidebar();
        } else {
            console.log('Closing sidebar');
            closeRightSidebarFunc();
        }
        
        return false;
    }, true);
    
    // Close button click
    closeRightSidebar?.addEventListener('click', closeRightSidebarFunc);
    
    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !rightSidebar.classList.contains('translate-x-full')) {
            closeRightSidebarFunc();
        }
    });
    
    // Initialize sidebar state - ensure it starts closed
    function initializeSidebarClosed() {
         rightSidebar.classList.add('translate-x-full');
         rightSidebar.style.transform = 'translateX(100%)';
         rightSidebar.style.visibility = 'hidden';
         if (toggleIcon) {
             toggleIcon.classList.remove('fa-chevron-right');
             toggleIcon.classList.add('fa-chevron-left');
         }
         rightSidebarToggle.style.right = '0';
         rightSidebarToggle.style.transform = 'translateY(-50%) translateX(25%)';
         console.log('Sidebar initialized as closed with transform:', rightSidebar.style.transform);
     }
    
    // Force initialization on every page load
    initializeSidebarClosed();
    
    // Also initialize after a small delay to ensure DOM is fully ready
    setTimeout(initializeSidebarClosed, 100);
});
</script>