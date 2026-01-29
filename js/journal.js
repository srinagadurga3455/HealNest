let selectedMood = 'neutral';
let currentEntryId = null;
let currentFilter = 'all';

// Mood emojis
const moodEmojis = {
    excellent: '😄',
    good: '😊',
    neutral: '😐',
    challenging: '😔',
    difficult: '😢'
};

// Writing prompts
const writingPrompts = [
    "What made you smile today?",
    "Describe a moment when you felt proud of yourself.",
    "What are three things you're grateful for right now?",
    "How did you overcome a challenge recently?",
    "What would you tell your younger self?",
    "Describe your ideal day from start to finish.",
    "What's something new you learned this week?",
    "How do you want to grow in the next month?",
    "What's a goal you're working towards?",
    "Describe a person who inspires you and why."
];

// Initialize page
document.addEventListener('DOMContentLoaded', function () {
    loadEntries();
    updateJournalStats();
    updatePopularTags();
    loadWritingPrompts();

    // Add mood selector handlers
    document.querySelectorAll('.mood-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.mood-btn').forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            selectedMood = this.dataset.mood;
        });
    });
});

function showNewEntryForm() {
    document.getElementById('defaultView').style.display = 'none';
    document.getElementById('entryView').classList.remove('active');
    document.getElementById('newEntryForm').classList.add('active');

    // Clear form
    document.getElementById('entryTitle').value = '';
    document.getElementById('entryContent').value = '';
    document.querySelectorAll('.mood-btn').forEach(btn => btn.classList.remove('selected'));
    document.querySelector('[data-mood="neutral"]').classList.add('selected');
    selectedMood = 'neutral';
}

function hideNewEntryForm() {
    document.getElementById('newEntryForm').classList.remove('active');
    document.getElementById('defaultView').style.display = 'block';
}

function showDefaultView() {
    document.getElementById('entryView').classList.remove('active');
    document.getElementById('newEntryForm').classList.remove('active');
    document.getElementById('defaultView').style.display = 'block';
}

function saveEntry(event) {
    event.preventDefault();

    const title = document.getElementById('entryTitle').value.trim();
    const content = document.getElementById('entryContent').value.trim();

    if (!title || !content) {
        alert('Please fill in both title and content.');
        return;
    }

    // Extract tags from content
    const tagMatches = content.match(/#(\w+)/g);
    const tags = tagMatches ? tagMatches.map(tag => tag.substring(1)) : [];

    const entryData = {
        title: title,
        content: content,
        mood: selectedMood,
        tags: tags,
        is_private: true
    };

    fetch('../api/journal.php?action=save_entry', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(entryData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Entry saved successfully!');
            
            // Refresh and go back to list
            loadEntries();
            updateJournalStats();
            updatePopularTags();
            hideNewEntryForm();
        } else {
            console.error('Failed to save entry:', data.message);
            // Fallback to localStorage
            try {
                Journal.saveEntry(title, content, selectedMood);
                alert('Entry saved successfully!');
                loadEntries();
                updateJournalStats();
                updatePopularTags();
                hideNewEntryForm();
            } catch (error) {
                alert('Error saving entry. Please try again.');
            }
        }
    })
    .catch(error => {
        console.error('Error saving entry:', error);
        // Fallback to localStorage
        try {
            Journal.saveEntry(title, content, selectedMood);
            alert('Entry saved successfully!');
            loadEntries();
            updateJournalStats();
            updatePopularTags();
            hideNewEntryForm();
        } catch (error) {
            alert('Error saving entry. Please try again.');
        }
    });
}

function loadEntries() {
    const filter = currentFilter !== 'all' ? `&filter=${currentFilter}` : '';
    const search = document.getElementById('searchInput')?.value || '';
    const searchParam = search ? `&search=${encodeURIComponent(search)}` : '';
    
    fetch(`../api/journal.php?action=get_entries${filter}${searchParam}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEntries(data.entries);
            } else {
                console.error('Failed to load entries:', data.message);
                // Fallback to localStorage
                loadEntriesFallback();
            }
        })
        .catch(error => {
            console.error('Error loading entries:', error);
            // Fallback to localStorage
            loadEntriesFallback();
        });
}

function loadEntriesFallback() {
    const entries = Journal.getEntries();
    displayEntries(entries);
}

function displayEntries(entries) {
    const entriesList = document.getElementById('entriesList');

    if (entries.length === 0) {
        entriesList.innerHTML = `
            <div class="text-center py-5">
                <i class="ti ti-book" style="font-size: 3rem; color: #6c757d;"></i>
                <h4 class="mt-3 text-muted">No journal entries yet</h4>
                <p class="text-muted">Start your journaling journey by creating your first entry.</p>
                <button class="btn-primary mt-3" onclick="showNewEntryForm()">
                    <i class="ti ti-plus me-2"></i>Write Your First Entry
                </button>
            </div>
        `;
        return;
    }

    entriesList.innerHTML = entries.map(entry => `
        <div class="entry-item" onclick="viewEntry(${entry.id})">
            <div class="entry-header">
                <div>
                    <div class="entry-title">${entry.title}</div>
                    <div class="entry-date">${formatDate(entry.createdAt || entry.created_at)}</div>
                </div>
                <div class="entry-mood">${moodEmojis[entry.mood]}</div>
            </div>
            <div class="entry-preview">${truncateText(entry.content, 100)}</div>
            ${entry.tags && entry.tags.length > 0 ? `
                <div class="entry-tags">
                    ${entry.tags.map(tag => `<span class="tag">#${tag}</span>`).join('')}
                </div>
            ` : ''}
        </div>
    `).join('');
}

function viewEntry(entryId) {
    fetch(`../api/journal.php?action=get_entry&entry_id=${entryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEntryView(data.entry);
            } else {
                console.error('Failed to load entry:', data.message);
                // Fallback to localStorage
                const entry = Journal.getEntry(entryId);
                if (entry) {
                    displayEntryView(entry);
                }
            }
        })
        .catch(error => {
            console.error('Error loading entry:', error);
            // Fallback to localStorage
            const entry = Journal.getEntry(entryId);
            if (entry) {
                displayEntryView(entry);
            }
        });
}

function displayEntryView(entry) {
    currentEntryId = entry.id;

    document.getElementById('defaultView').style.display = 'none';
    document.getElementById('newEntryForm').classList.remove('active');
    document.getElementById('entryView').classList.add('active');

    const createdAt = entry.createdAt || entry.created_at;
    document.getElementById('entryViewContent').innerHTML = `
        <h2>${entry.title}</h2>
        <div class="entry-meta">
            <div>
                <small class="text-muted">
                    <i class="ti ti-calendar me-1"></i>${formatDateTime(createdAt)}
                </small>
            </div>
            <div>
                <span class="entry-mood" style="font-size: 1.5rem;">${moodEmojis[entry.mood]}</span>
            </div>
        </div>
        <div class="entry-content">${formatContent(entry.content)}</div>
        ${entry.tags && entry.tags.length > 0 ? `
            <div class="entry-tags mt-3">
                ${entry.tags.map(tag => `<span class="tag">#${tag}</span>`).join('')}
            </div>
        ` : ''}
    `;
}

function editEntry() {
    const entry = Journal.getEntry(currentEntryId);
    if (!entry) return;

    // Populate form with current entry data
    document.getElementById('entryTitle').value = entry.title;
    document.getElementById('entryContent').value = entry.content;

    // Set mood
    document.querySelectorAll('.mood-btn').forEach(btn => btn.classList.remove('selected'));
    document.querySelector(`[data-mood="${entry.mood}"]`).classList.add('selected');
    selectedMood = entry.mood;

    // Show form
    document.getElementById('entryView').classList.remove('active');
    document.getElementById('newEntryForm').classList.add('active');

    // Delete old entry when saving
    const originalSaveEntry = window.saveEntry;
    window.saveEntry = function (event) {
        event.preventDefault();

        const title = document.getElementById('entryTitle').value.trim();
        const content = document.getElementById('entryContent').value.trim();

        if (!title || !content) {
            alert('Please fill in both title and content.');
            return;
        }

        try {
            // Delete old entry
            Journal.deleteEntry(currentEntryId);

            // Save new entry
            Journal.saveEntry(title, content, selectedMood);

            alert('Entry updated successfully!');

            // Refresh and go back to list
            loadEntries();
            updateJournalStats();
            updatePopularTags();
            hideNewEntryForm();

            // Restore original save function
            window.saveEntry = originalSaveEntry;

        } catch (error) {
            alert('Error updating entry. Please try again.');
        }
    };
}

function deleteEntry() {
    if (!currentEntryId) return;

    if (confirm('Are you sure you want to delete this entry? This action cannot be undone.')) {
        fetch('../api/journal.php?action=delete_entry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                entry_id: currentEntryId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Entry deleted successfully.');
                
                loadEntries();
                updateJournalStats();
                updatePopularTags();
                showDefaultView();
            } else {
                console.error('Failed to delete entry:', data.message);
                // Fallback to localStorage
                try {
                    Journal.deleteEntry(currentEntryId);
                    alert('Entry deleted successfully.');
                    loadEntries();
                    updateJournalStats();
                    updatePopularTags();
                    showDefaultView();
                } catch (error) {
                    alert('Error deleting entry. Please try again.');
                }
            }
        })
        .catch(error => {
            console.error('Error deleting entry:', error);
            // Fallback to localStorage
            try {
                Journal.deleteEntry(currentEntryId);
                alert('Entry deleted successfully.');
                loadEntries();
                updateJournalStats();
                updatePopularTags();
                showDefaultView();
            } catch (error) {
                alert('Error deleting entry. Please try again.');
            }
        });
    }
}

function searchEntries() {
    loadEntries();
}

function filterEntries(filter) {
    currentFilter = filter;

    // Update filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-filter="${filter}"]`).classList.add('active');

    loadEntries();
}

function updateJournalStats() {
    fetch('../api/journal.php?action=get_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayJournalStats(data.stats);
            } else {
                console.error('Failed to load stats:', data.message);
                // Fallback to localStorage
                updateJournalStatsFallback();
            }
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            // Fallback to localStorage
            updateJournalStatsFallback();
        });
}

function displayJournalStats(stats) {
    const statsContainer = document.getElementById('journalStats');
    if (!statsContainer) return;

    statsContainer.innerHTML = `
        <div class="stat-item">
            <div class="stat-number">${stats.total_entries}</div>
            <div class="stat-label">Total Entries</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">${stats.week_entries}</div>
            <div class="stat-label">This Week</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">${stats.avg_words}</div>
            <div class="stat-label">Avg Words</div>
        </div>
    `;
}

function updateJournalStatsFallback() {
    const entries = Journal.getEntries();
    const statsContainer = document.getElementById('journalStats');
    if (!statsContainer) return;

    // Calculate stats
    const totalEntries = entries.length;
    const thisWeek = entries.filter(entry => {
        const entryDate = new Date(entry.createdAt);
        const weekAgo = new Date();
        weekAgo.setDate(weekAgo.getDate() - 7);
        return entryDate >= weekAgo;
    }).length;

    const totalWords = entries.reduce((sum, entry) => sum + entry.content.split(' ').length, 0);
    const avgWords = totalEntries > 0 ? Math.round(totalWords / totalEntries) : 0;

    statsContainer.innerHTML = `
        <div class="stat-item">
            <div class="stat-number">${totalEntries}</div>
            <div class="stat-label">Total Entries</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">${thisWeek}</div>
            <div class="stat-label">This Week</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">${avgWords}</div>
            <div class="stat-label">Avg Words</div>
        </div>
    `;
}

function updatePopularTags() {
    fetch('../api/journal.php?action=get_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPopularTags(data.stats.popular_tags);
            } else {
                console.error('Failed to load tags:', data.message);
                // Fallback to localStorage
                updatePopularTagsFallback();
            }
        })
        .catch(error => {
            console.error('Error loading tags:', error);
            // Fallback to localStorage
            updatePopularTagsFallback();
        });
}

function displayPopularTags(tagCounts) {
    const tagsContainer = document.getElementById('popularTags');
    if (!tagsContainer) return;

    if (Object.keys(tagCounts).length === 0) {
        tagsContainer.innerHTML = '<p class="text-muted">No tags yet. Use #hashtags in your entries!</p>';
        return;
    }

    const sortedTags = Object.entries(tagCounts).slice(0, 10);
    tagsContainer.innerHTML = sortedTags.map(([tag, count]) => `
        <span class="tag me-1 mb-1" style="cursor: pointer;" onclick="searchByTag('${tag}')">
            #${tag} (${count})
        </span>
    `).join('');
}

function updatePopularTagsFallback() {
    const entries = Journal.getEntries();
    const tagCounts = {};

    entries.forEach(entry => {
        entry.tags.forEach(tag => {
            tagCounts[tag] = (tagCounts[tag] || 0) + 1;
        });
    });

    displayPopularTags(tagCounts);
}

function loadWritingPrompts() {
    const promptsList = document.getElementById('promptsList');
    const randomPrompts = writingPrompts.sort(() => 0.5 - Math.random()).slice(0, 3);

    promptsList.innerHTML = randomPrompts.map(prompt => `
                <div class="mb-2">
                    <small class="text-muted" style="cursor: pointer;" onclick="usePrompt('${prompt}')">
                        "${prompt}"
                    </small>
                </div>
            `).join('');
}

function usePrompt(prompt) {
    showNewEntryForm();
    document.getElementById('entryContent').value = prompt + '\n\n';
    document.getElementById('entryContent').focus();
}

function searchByTag(tag) {
    document.getElementById('searchInput').value = '#' + tag;
    searchEntries();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday';
    } else {
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: date.getFullYear() !== today.getFullYear() ? 'numeric' : undefined
        });
    }
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatContent(content) {
    // Convert line breaks to paragraphs
    return content.split('\n\n').map(paragraph =>
        `<p>${paragraph.replace(/\n/g, '<br>')}</p>`
    ).join('');
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}