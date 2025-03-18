function updateLiveCounters() {
    // Simulating fetched data (replace with an API call later)
    let totalCustomers = Math.floor(Math.random() * 100) + 1; 
    let totalDiscounts = (Math.random() * 5000).toFixed(2); 

    document.getElementById("totalCustomers").textContent = totalCustomers;
    document.getElementById("totalDiscounts").textContent = `₱${totalDiscounts}`;
}

