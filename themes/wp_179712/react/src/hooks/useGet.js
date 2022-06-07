import useSWR from "swr"

export const useGet = (url) => {
    const baseUrl = `${window.location.origin}/wp-json/`
    const fetcher = (url) => fetch(url).then((res) => res.json())
    return useSWR(`${baseUrl}${url}`, fetcher)
}
