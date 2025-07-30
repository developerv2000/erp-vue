import { useMADManufacturerTableStore } from "../stores/useMADManufacturerTableStore"
import axios from "axios"

export function useMADManufacturerTable() {
    const store = useMADManufacturerTableStore()

    async function fetchRecords(loadingRef) {
        if (loadingRef) loadingRef.value = true

        try {
            const response = await axios.get('/api/manufacturers', {
                params: store.toQuery(),
            })
            store.updateAfterFetch(response)
        } finally {
            if (loadingRef) loadingRef.value = false
        }
    }

    return {
        store,
        fetchRecords,
    }
}
