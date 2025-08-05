import { useMADManufacturerTableStore } from "../stores/useMADManufacturerTableStore"
import axios from "axios"

export function useMADManufacturerTable() {
    const store = useMADManufacturerTableStore()

    function fetchRecords() {
        store.loading = true;

        axios.get('/api/manufacturers', {
            params: store.toQuery(),
        })
            .then(response => {
                store.updateStateAfterFetch(response);
            })
            .finally(() => {
                store.loading = false;
            })
    }

    return {
        store,
        fetchRecords,
    }
}
