document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('sidebarToggle');
    const mainContent = document.getElementById('mainContent');

    function setInitialState() {
        if (window.innerWidth < 768) {
            sidebar.classList.add('sidebar-hidden');
            sidebar.classList.remove('sidebar-visible');
            toggleButton.classList.add('toggle-default');
            toggleButton.classList.remove('toggle-moved');
            mainContent.classList.add('content-full');
            mainContent.classList.remove('content-shifted');
        } else {
            sidebar.classList.remove('sidebar-hidden');
            sidebar.classList.add('sidebar-visible');
            toggleButton.classList.remove('toggle-default');
            toggleButton.classList.add('toggle-moved');
            mainContent.classList.remove('content-full');
            mainContent.classList.add('content-shifted');
        }
    }

    setInitialState();
    window.addEventListener('resize', setInitialState);

    toggleButton.addEventListener('click', function() {
        if (sidebar.classList.contains('sidebar-hidden')) {
            sidebar.classList.remove('sidebar-hidden');
            sidebar.classList.add('sidebar-visible');
            toggleButton.classList.remove('toggle-default');
            toggleButton.classList.add('toggle-moved');
            mainContent.classList.remove('content-full');
            mainContent.classList.add('content-shifted');
        } else {
            sidebar.classList.add('sidebar-hidden');
            sidebar.classList.remove('sidebar-visible');
            toggleButton.classList.add('toggle-default');
            toggleButton.classList.remove('toggle-moved');
            mainContent.classList.add('content-full');
            mainContent.classList.remove('content-shifted');
        }
    });
});