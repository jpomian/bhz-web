const leaderboardTable = document.getElementById('leaderboardTable');
const playerSearch = document.getElementById('playerSearch');
const prevPageBtn = document.getElementById('prevPage');
const nextPageBtn = document.getElementById('nextPage');
const pageInput = document.getElementById('pageInput');

let currentPage = 1;
let totalPages = 1;
const playersPerPage = 25;
let currentSort = 'Infections';
let currentOrder = 'DESC';

function fetchLeaderboard(page, search = '', sort = currentSort, order = currentOrder) {
    fetch(`sql-stats.php?page=${page}&limit=${playersPerPage}&search=${search}&sort=${sort}&order=${order}`)
        .then(response => response.json())
        .then(data => {
            updateLeaderboard(data.players, search);
            updatePagination(data.totalPlayers);
        })
        .catch(error => console.error('Error:', error));
}

function updateLeaderboard(players, search) {
    const tbody = leaderboardTable.querySelector('tbody');
    tbody.innerHTML = '';

    players.forEach((player, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${player.rank}</td>
            <td>${search ? '' : getMedalBadge(player.rank)}${getBoldText(player.Nick)}</td>
            <td>${player.Infections}</td>
            <td>${player.Kills}</td>
            <td>${formatTime(player.Time)}</td>
            <td>${timeAgo(player.Last)}</td>
        `;
        tbody.appendChild(row);
    });
}

function getMedalBadge(rank) {
    if (rank === 1) return '<span class="medal gold"></span>';
    if (rank === 2) return '<span class="medal silver"></span>';
    if (rank === 3) return '<span class="medal bronze"></span>';
    return '';
}

function getBoldText(name) {
    return `<b>${name}</b>`
}

function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);

    if (hours < 1) {
        return `${minutes}min`;
    } else {
        return `${hours}h ${minutes}m`;
    }
}

function timeAgo(timestamp) {
    const now = new Date();
    const lastSeen = new Date(timestamp * 1000); // Convert Unix timestamp to milliseconds
    const interval = now - lastSeen;

    const seconds = Math.floor(interval / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) {
        return days + ' dni temu';
    } else if (hours > 0) {
        return hours + ' godzin temu';
    } else {
        return minutes + ' minut temu';
    }
}

function updatePagination(totalPlayers) {
    totalPages = Math.ceil(totalPlayers / playersPerPage);
    pageInput.max = totalPages;
    prevPageBtn.disabled = currentPage === 1;
    nextPageBtn.disabled = currentPage === totalPages;
}

playerSearch.addEventListener('input', debounce(() => {
    currentPage = 1;
    pageInput.value = currentPage;
    fetchLeaderboard(currentPage, playerSearch.value);
}, 300));

prevPageBtn.addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        pageInput.value = currentPage;
        fetchLeaderboard(currentPage, playerSearch.value);
    }
});

nextPageBtn.addEventListener('click', () => {
    if (currentPage < totalPages) {
        currentPage++;
        pageInput.value = currentPage;
        fetchLeaderboard(currentPage, playerSearch.value);
    }
});

pageInput.addEventListener('change', () => {
    const newPage = parseInt(pageInput.value);
    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        fetchLeaderboard(currentPage, playerSearch.value);
    }
});

leaderboardTable.querySelector('thead').addEventListener('click', (e) => {
    const th = e.target.closest('th');
    if (th) {
        const column = th.textContent.trim();
        if (column === currentSort) {
            currentOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = column;
            currentOrder = 'DESC';
        }
        fetchLeaderboard(currentPage, playerSearch.value, currentSort, currentOrder);
    }
});

function debounce(func, delay) {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Initial fetch
fetchLeaderboard(currentPage);
