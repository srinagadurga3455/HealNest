// Simple Journal Utility
const Journal = {
    // Get all journal entries
    getEntries() {
        const entries = localStorage.getItem('healNestJournalEntries');
        if (entries) {
            return JSON.parse(entries);
        }
        
        // Return sample entries for demo
        return [
            {
                id: 1,
                title: 'My First Day',
                content: 'Started my wellness journey today. Feeling optimistic about the changes ahead.',
                mood: 'positive',
                tags: ['wellness', 'journey'],
                created_at: new Date().toISOString(),
                is_private: true
            },
            {
                id: 2,
                title: 'Meditation Session',
                content: 'Had a great 10-minute meditation session this morning. Feeling more centered.',
                mood: 'calm',
                tags: ['meditation', 'mindfulness'],
                created_at: new Date(Date.now() - 86400000).toISOString(), // Yesterday
                is_private: true
            }
        ];
    },

    // Add new entry
    addEntry(entry) {
        const entries = this.getEntries();
        entry.id = Date.now();
        entry.created_at = new Date().toISOString();
        entries.unshift(entry);
        localStorage.setItem('healNestJournalEntries', JSON.stringify(entries));
        return entry;
    },

    // Get entry by ID
    getEntry(id) {
        const entries = this.getEntries();
        return entries.find(entry => entry.id === parseInt(id));
    },

    // Update entry
    updateEntry(id, updatedEntry) {
        const entries = this.getEntries();
        const index = entries.findIndex(entry => entry.id === parseInt(id));
        if (index !== -1) {
            entries[index] = { ...entries[index], ...updatedEntry, updated_at: new Date().toISOString() };
            localStorage.setItem('healNestJournalEntries', JSON.stringify(entries));
            return entries[index];
        }
        return null;
    },

    // Delete entry
    deleteEntry(id) {
        const entries = this.getEntries();
        const filteredEntries = entries.filter(entry => entry.id !== parseInt(id));
        localStorage.setItem('healNestJournalEntries', JSON.stringify(filteredEntries));
        return true;
    }
};