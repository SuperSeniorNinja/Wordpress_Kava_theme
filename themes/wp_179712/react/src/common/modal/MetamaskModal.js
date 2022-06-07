import { useWalletModal } from "./WalletModal"
import { useMetaMask } from "metamask-react"
import MetamaskImage from "../../../assets/svg/metamask.svg"

export const useMetamaskModal = () => {
    const metaMask = useMetaMask()
    const content = () => <Metamask metamask={metaMask} />
    const header = "Connect to your wallet"
    const walletModal = useWalletModal(content, header)

    return {
        ...walletModal,
        ...metaMask,
    }
}

const Metamask = ({ metamask }) => {
    const { connect, status, account } = metamask

    const onClick = async () => {
        if (status !== "connected") {
            await connect()
        }
    }

    return (
        <div className="nanoverse-modal-wallets">
            <div className="nanoverse-modal-wallet" onClick={onClick}>
                {status === "connected" && <i className="fas fa-3x fa-check-circle nanoverse-modal-wallet-connected"></i>}
                <img src={MetamaskImage} />
                {status === "connected" && <div className="nanoverse-modal-account">{account}</div>}
            </div>
        </div>
    )
}
