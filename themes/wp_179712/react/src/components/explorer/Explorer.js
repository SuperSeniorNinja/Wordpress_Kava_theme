import { useGet } from "../../hooks/useHttp"
import "./index.scss"
import HashLoader from "react-spinners/HashLoader"
import { useForm, FormProvider, useFormContext } from "react-hook-form"
import { useWeb3Auth } from "../../hooks/useWeb3Auth"

const { useState, useEffect } = wp.element

export const Explorer = () => {
    return (
        <>
            <div className="explorer-container">
                <div className="explorer-header">My NFTs</div>
                <Metamask />
            </div>
        </>
    )
}

const NftsExplorer = ({ address }) => {
    const methods = useForm()
    const watchedFields = methods.watch()
    const [url, setUrl] = useState(`tatum/v1/explorer?chain=CELO,ETH,MATIC,XDC,BSC&address=${address}`)
    const [emptyFilter, setEmptyFilter] = useState(false)

    useEffect(() => {
        if (Object.keys(watchedFields).length > 0) {
            const asArray = Object.entries(watchedFields)
            const filtered = asArray
                .filter(([key, value]) => key.startsWith("tatum-explorer-filter-chain") && value === true)
                .map(([key, value]) => key.split("tatum-explorer-filter-chain-")[1])
            if (filtered.length === 0) {
                setEmptyFilter(true)
            } else {
                setUrl(`tatum/v1/explorer?chains=${filtered.toString()}&address=${address}`)
                setEmptyFilter(false)
            }
        }
    }, [watchedFields])

    const { data } = useGet(url)
    return (
        <div className="explorer-content">
            <FormProvider {...methods}>
                <Filter />
                {!data && !emptyFilter && <Spinner isLoading={!data} />}
                {data && <Nfts nfts={data} emptyFilter={emptyFilter} />}
            </FormProvider>
        </div>
    )
}

const Nfts = ({ nfts, emptyFilter }) => {
    return (
        <div className="explorer-nfts">
            {!emptyFilter ? <NftsList nfts={Object.entries(nfts)} /> : <div>Please adjust filter settings</div>}
        </div>
    )
}

const NftsList = ({ nfts }) => {
    return <div>{nfts.length > 0 ? <MappedNfts nfts={nfts} /> : <div>Cannot find no data.</div>}</div>
}

const MappedNfts = ({ nfts }) => {
    return nfts.map(([chain, nftsMetadata]) => {
        return nftsMetadata.map((nft) => {
            return nft.metadata.filter((metadata) => metadata?.metadata?.image).map((metadata) => <Nft metadata={metadata} chain={chain} />)
        })
    })
}

const Nft = ({ metadata, chain }) => {
    return (
        <div className="explorer-nft">
            <img className="explorer-nft-image" src={`https://ipfs.io/ipfs/${metadata?.metadata?.image.split("ipfs://")[1]}`} />
            <div className="explorer-nft-name">{metadata?.metadata?.name}</div>
            <div>Chain: {chain}</div>
        </div>
    )
}

const Filter = () => {
    return (
        <div className="explorer-filter">
            <form>
                <FilterChains />
            </form>
        </div>
    )
}

const FilterChains = () => {
    const chains = ["CELO", "ETH", "MATIC", "XDC", "BSC"]
    return (
        <div className="explorer-filter-chains">
            <h5 className="explorer-filter-chains-header">Chains</h5>
            <div className="explorer-filter-chains-container">
                {chains.map((chain) => (
                    <FilterChainInput chain={chain} />
                ))}
            </div>
        </div>
    )
}

const FilterChainInput = ({ chain }) => {
    const { register } = useFormContext()
    return (
        <div className="explorer-filter-chains-chain">
            <input
                {...register(`tatum-explorer-filter-chain-${chain}`)}
                defaultChecked={true}
                type="checkbox"
                className="explorer-filter-chains-chain-input"
            />
            {chain}
        </div>
    )
}

const Spinner = ({ isLoading }) => {
    return (
        <div className="explorer-spinner">
            <HashLoader color="#0C5ADB" loading={isLoading} size={150} />
        </div>
    )
}

const Metamask = () => {
    const { account, login } = useWeb3Auth()
    return (
        <>
            {account ? (
                <NftsExplorer address={account} />
            ) : (
                <>
                    <div className="explorer-connect-metamask" onClick={login}>
                        Connect to Wallet
                    </div>
                </>
            )}
        </>
    )
}
