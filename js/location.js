function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
            },
            function(error) {
                console.log('Error getting location:', error.message);
            }
        );
    } else {
        console.log('Geolocation is not supported by this browser.');
    }
}

// Call getLocation when the page loads
window.addEventListener('load', getLocation);