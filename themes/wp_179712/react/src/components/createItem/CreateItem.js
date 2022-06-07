import "./index.scss"
import { useForm, FormProvider } from "react-hook-form"
import { baseUrl, mutate } from "../../hooks/useHttp"
import { useModal } from "../../common/modal/Modal"
import { useWeb3Auth } from "../../hooks/useWeb3Auth"
const { useState } = wp.element

export const CreateItem = () => {
    return (
        <div className="create-item">
            <div className="create-item-header">Create An Item</div>
            <div className="create-item-description">
                Now you can list your item for free, there are two options available for that if the item is already in your wallet or you
                want to create a new one.
            </div>
            <CreateOrAddNFT />
        </div>
    )
}

const AddNftModal = () => {
    const { account } = useWeb3Auth()
    const {
        register,
        handleSubmit,
        formState: { errors },
    } = useForm()
    const [response, setResponse] = useState()
    const [sending, setSending] = useState(false)
    const onSubmit = async (data) => {
        setSending(true)
        const response = await mutate("tatum/v1/explorer/add-minted", { ...data, address: account }, "POST")
        setResponse(response)
        setSending(false)
    }
    return (
        <div className="add-nft-to-wallet">
            <div className="add-nft-to-wallet-description">
                This is for items you already own in your wallet. Start by entering the Token ID and Address of the item on the blockchain
            </div>
            <form onSubmit={handleSubmit(onSubmit)} className="add-nft-to-wallet-form">
                <label className="add-nft-to-wallet-label">
                    Token ID
                    <input {...register("tokenId", { required: true })} className="add-nft-to-wallet-text-input" />
                    {errors.tokenId && <span className="add-nft-to-wallet-label-error">This field is required</span>}
                </label>

                <label className="add-nft-to-wallet-label">
                    Smart Contract Address
                    <input {...register("smartContractAddress", { required: true })} className="add-nft-to-wallet-text-input" />
                    {errors.smartContractAddress && <span className="add-nft-to-wallet-label-error">This field is required</span>}
                </label>

                <label className="add-nft-to-wallet-label">
                    Chain
                    <select
                        {...register("chain", {
                            required: "true",
                        })}
                        className="add-nft-to-wallet-text-input"
                    >
                        <option value="XDC">XDC</option>
                        <option value="BSC">BSC</option>
                    </select>
                    {errors.chain && <span className="add-nft-to-wallet-label-error">This field is required</span>}
                </label>
                <button type="submit" className="add-nft-to-wallet-submit" disabled={sending}>
                    {sending ? "Sending..." : "Add"}
                </button>
                {response?.message && <div>{response?.message}</div>}
                {response?.tokenId && <div>Your NFT was added to the Odyssea</div>}
            </form>
        </div>
    )
}

const MintNftModal = () => {
    const methods = useForm()
    const {
        register,
        handleSubmit,
        formState: { errors },
    } = methods
    const { account } = useWeb3Auth()
    const [sending, setSending] = useState(false)
    const [txId, setTxId] = useState()

    const [fileName, setFileName] = useState()

    const displaySelectedFile = (event) => {
        setFileName(event.target.files[0].name)
    }

    const onSubmit = async (data) => {
        setSending(true)
        const formData = new FormData()
        formData.append("name", data.name)
        formData.append("description", data.description)
        // TODO: sign with private key from metamask
        formData.append("privateKey", data.privateKey)
        formData.append("file", data.file[0])
        formData.append("address", account)
        const response = await fetch(`${baseUrl}tatum/v1/explorer/mint`, {
            method: "POST",
            body: formData,
        }).then((res) => res.json())
        setTxId(response.txId)
        setSending(false)
    }
    return (
        <FormProvider {...methods}>
            <div className="add-nft-to-wallet">
                <form onSubmit={handleSubmit(onSubmit)} className="add-nft-to-wallet-form">
                    <label className="add-nft-to-wallet-label">
                        Name
                        <input {...register("name", { required: true })} className="add-nft-to-wallet-text-input" />
                        {errors.name && <span className="add-nft-to-wallet-label-error">This field is required</span>}
                    </label>

                    <label className="add-nft-to-wallet-label">
                        Description
                        <input {...register("description", { required: true })} className="add-nft-to-wallet-text-input" />
                        {errors.description && <span className="add-nft-to-wallet-label-error">This field is required</span>}
                    </label>

                    <label className="add-nft-to-wallet-label">
                        Private Key
                        <input {...register("privateKey", { required: true })} className="add-nft-to-wallet-text-input" />
                        {errors.privateKey && <span className="add-nft-to-wallet-label-error">This field is required</span>}
                    </label>

                    <div className="add-nft-to-wallet-label">
                        <div className="add-nft-to-wallet-label-file">
                            <label htmlFor="image-input" className="add-nft-to-wallet-label-file-label">
                                Image
                            </label>
                            <input
                                className="add-nft-to-wallet-label-file-input"
                                id="image-input"
                                type="file"
                                {...register("file", { required: true })}
                                onChange={displaySelectedFile}
                            />
                            {fileName && <div>{fileName}</div>}
                        </div>
                        {errors.file && <div className="add-nft-to-wallet-label-error">This field is required</div>}
                    </div>
                    <label className="add-nft-to-wallet-label">
                        Chain
                        <select
                            {...register("chain", {
                                required: "true",
                            })}
                            className="add-nft-to-wallet-text-input"
                        >
                            <option value="XDC">XDC</option>
                        </select>
                        {errors.chain && <span className="add-nft-to-wallet-label-error">This field is required</span>}
                    </label>
                    <button type="submit" className="add-nft-to-wallet-submit" disabled={sending}>
                        {sending ? "Sending..." : "Mint"}
                    </button>
                    {txId && <div>You NFT was minted with transaction {txId}</div>}
                </form>
            </div>
        </FormProvider>
    )
}

const CreateOrAddNFT = () => {
    const { Modal: AddNftModalDisplay, setOpen: setOpenAddNftModal } = useModal(AddNftModal, "Identify This Item On The Blockchain")
    const { Modal: CreateNftModalDisplay, setOpen: setOpenMintNftModal } = useModal(MintNftModal, "Mint NFT")

    return (
        <div className="create-item-create-or-add">
            <ActionBox
                label="The NFT Is Already In My Wallet"
                imageUrl="/wp-content/uploads/2021/11/right-arrow.png"
                onClick={() => setOpenAddNftModal(true)}
            />
            <ActionBox
                label="Create A New Item"
                imageUrl="/wp-content/uploads/2021/11/writing.png"
                onClick={() => setOpenMintNftModal(true)}
            />
            <AddNftModalDisplay />
            <CreateNftModalDisplay />
        </div>
    )
}

const ActionBox = ({ imageUrl, label, onClick }) => {
    return (
        <div className="create-item-action-box" onClick={onClick}>
            <div className="create-item-action-box-image-container">
                <img
                    width="100"
                    height="100"
                    src={`${window.location.origin}${imageUrl}`}
                    className="attachment-full size-full"
                    alt=""
                    loading="lazy"
                />
            </div>
            <div className="create-item-action-box-text">{label}</div>
        </div>
    )
}
