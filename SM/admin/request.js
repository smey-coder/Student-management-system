let lastRequestId = 0;

// Initialize lastRequestId with highest current ID
document.addEventListener("DOMContentLoaded", function() {
    const rows = document.querySelectorAll("table tbody tr");
    rows.forEach(row => {
        const id = parseInt(row.cells[0].textContent);
        if(id > lastRequestId) lastRequestId = id;
    });
});

// Poll every 10 seconds
setInterval(() => {
    fetch(`check_new_requests.php?last_id=${lastRequestId}`)
        .then(response => response.json())
        .then(data => {
            if(data.length > 0) {
                data.forEach(req => {
                    alert(`New request from ${req.student_name}\nType: ${req.request_type}`);
                    lastRequestId = Math.max(lastRequestId, req.id);
                });
            }
        })
        .catch(err => console.error(err));
}, 10000);
