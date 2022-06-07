import useSWR from "swr"

export const baseUrl = `${window.location.origin}/wp-json/`

export const useGet = (url, method = "GET") => {
    const fetcher = (url) => fetch(url, { method }).then((res) => res.json())
    return useSWR(`${baseUrl}${url}`, fetcher)
}

export const mutate = (url, body, method) => fetch(`${baseUrl}${url}`, { method, body: JSON.stringify(body) }).then((res) => res.json())
