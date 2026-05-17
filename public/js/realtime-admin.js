let currentPendingCount = null;

function updateStats() {
    fetch('api/dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) return;
            const totalAlumniEl = document.getElementById('total-alumni');
            const totalUsersEl = document.getElementById('total-users');
            const totalPendingEl = document.getElementById('total-pending');

            if (totalAlumniEl) totalAlumniEl.innerText = data.totalAlumni.toLocaleString();
            if (totalUsersEl) totalUsersEl.innerText = data.totalUsers.toLocaleString();
            if (totalPendingEl) totalPendingEl.innerText = data.pending.toLocaleString();
        })
        .catch(err => console.error('Error fetching stats:', err));
}

function updatePendingRequests() {
    fetch('api/pending_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) return;
            
            // Update badge
            const badge = document.getElementById('badge-pending');
            if (badge) badge.innerText = data.count;
            
            // Show toast if count increased
            if (currentPendingCount !== null && data.count > currentPendingCount) {
                showToast(`${data.count - currentPendingCount} request akun baru!`);
            }
            currentPendingCount = data.count;
            
            // Update table
            const tbody = document.getElementById('pending-users-table');
            if (tbody) {
                if (data.count === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Tidak ada request pending</td></tr>';
                } else {
                    let html = '';
                    data.users.forEach(user => {
                        html += `
                        <tr>
                            <td><code>${user.nis || '-'}</code></td>
                            <td>${user.nama || '-'}</td>
                            <td>${user.jurusan || '-'}</td>
                            <td>${user.angkatan || '-'}</td>
                            <td>${user.username}</td>
                            <td>
                                <div class="action-btns">
                                    <a href="delete_user.php?action=approve&id=${user.user_id}" class="btn-sm btn-success" onclick="return confirm('Setujui pendaftaran ini?')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                        Setujui
                                    </a>
                                    <a href="delete_user.php?action=reject&id=${user.user_id}" class="btn-sm btn-danger" onclick="return confirm('Tolak pendaftaran ini?')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        Tolak
                                    </a>
                                </div>
                            </td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;
                }
            }
        })
        .catch(err => console.error('Error fetching pending requests:', err));
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerText = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Inject CSS untuk Toast
const style = document.createElement('style');
style.innerHTML = `
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: rgba(220, 38, 38, 0.9);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    z-index: 1000;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    animation: fadeIn 0.3s forwards;
}
@keyframes fadeIn { 
    from { opacity: 0; transform: translateY(10px); } 
    to { opacity: 1; transform: translateY(0); } 
}
@keyframes fadeOut { 
    from { opacity: 1; transform: translateY(0); } 
    to { opacity: 0; transform: translateY(10px); } 
}
`;
document.head.appendChild(style);

// Initial load
updateStats();
updatePendingRequests();

// Polling setiap 5 detik
setInterval(updateStats, 5000);
setInterval(updatePendingRequests, 5000);
