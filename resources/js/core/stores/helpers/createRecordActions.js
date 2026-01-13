export function createRecordActions() {
    return {
        /**
         * Update or insert a record in the store's records array.
         *
         * @param {Object} updatedRecord - The record object returned from the backend.
         */
        updateRecord(updatedRecord) {
            if (!updatedRecord || !updatedRecord.id) return;

            const index = this.records.findIndex(r => r.id === updatedRecord.id);

            if (index !== -1) {
                // Replace the existing record (keeps reactivity)
                this.records[index] = updatedRecord;
            } else {
                // Insert new record at the top (optional behavior)
                this.records.unshift(updatedRecord);
            }
        },

        /**
         * Update multiple records at once (useful for batch updates).
         */
        updateRecords(updatedRecords = []) {
            updatedRecords.forEach(this.updateRecord);
        },

        /**
         * Remove a record by ID.
         */
        removeRecord(id) {
            const index = this.records.findIndex(r => r.id === id);
            if (index !== -1) this.records.splice(index, 1);
        },
    };
}
