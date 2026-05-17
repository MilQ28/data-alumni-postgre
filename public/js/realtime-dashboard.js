function updateAlumniGrid() {
    fetch('api/latest_alumni.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) return;
            
            const grid = document.getElementById('alumni-grid');
            if (!grid) return;
            
            if (data.length === 0) {
                grid.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <p>Tidak ada alumni yang ditemukan.</p>
                </div>`;
            } else {
                let html = '';
                data.forEach(a => {
                    // Cek apakah ini kartu milik user yang sedang login
                    const isMyCard = (typeof myIdAlumni !== 'undefined' && a.id_alumni == myIdAlumni);
                    
                    html += `
                    <div class="alumni-card ${isMyCard ? 'my-card' : ''}">
                        <div class="alumni-card-top">
                            <div class="alumni-avatar">
                                ${a.foto_profil ? 
                                    `<img src="uploads/foto_profil/${a.foto_profil}" alt="Foto">` : 
                                    `<div class="avatar-letter">${a.nama.charAt(0).toUpperCase()}</div>`
                                }
                            </div>
                            ${isMyCard ? `<span class="my-badge">Saya</span>` : ''}
                        </div>
                        <div class="alumni-card-body">
                            <h4>${a.nama}</h4>
                            <p class="alumni-nis"><code>${a.nis}</code></p>
                            <span class="tag">${a.jurusan}</span>
                            <div class="alumni-meta">
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    ${a.angkatan}
                                </span>
                                ${a.pekerjaan ? `
                                <span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                    ${a.pekerjaan}
                                </span>` : ''}
                            </div>
                            ${isMyCard ? `
                            <a href="profile.php" class="btn-sm btn-edit" style="margin-top:10px;display:inline-flex">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Data Saya
                            </a>` : ''}
                        </div>
                    </div>`;
                });
                grid.innerHTML = html;
            }
        })
        .catch(err => console.error('Error fetching alumni:', err));
}

// Initial load
updateAlumniGrid();

// Polling setiap 5 detik
setInterval(updateAlumniGrid, 5000);
