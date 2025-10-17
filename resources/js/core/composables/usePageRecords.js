import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";

/**
 * usePageRecords
 * A helper composable for managing and updating records from Inertia pages.
 *
 * Usage:
 * const { records, updateRecord } = usePageRecords();
 * updateRecord(updatedRecord);
 */
export function usePageRecords() {
    const page = usePage();

    // Ensure local reactivity — Inertia page props aren't deeply reactive
    const records = ref([...page.props.records]);

    /**
     * Replace or insert an updated record into the local records array.
     * @param {Object} updatedRecord - The fresh record data from the backend.
     */
    function updateRecord(updatedRecord) {
        if (!updatedRecord || !updatedRecord.id) return;

        const index = records.value.findIndex(r => r.id === updatedRecord.id);

        if (index !== -1) {
            // Replace existing record
            records.value[index] = updatedRecord;
        } else {
            // Insert new one (if missing)
            records.value.unshift(updatedRecord);
        }
    }

    /**
     * Replace multiple records at once.
     * @param {Array<Object>} updatedRecords
     */
    function updateRecords(updatedRecords = []) {
        updatedRecords.forEach(updateRecord);
    }

    /**
     * Remove a record by ID
     * @param {Number|String} id
     */
    function removeRecord(id) {
        const index = records.value.findIndex(r => r.id === id);
        if (index !== -1) records.value.splice(index, 1);
    }

    return {
        records,
        updateRecord,
        updateRecords,
        removeRecord,
    };
}
