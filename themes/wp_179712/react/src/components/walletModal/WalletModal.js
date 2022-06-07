import "./index.scss"
import { MetaMaskProvider } from "metamask-react"
import { useMetamaskModal } from "../../common/modal/MetamaskModal"

export const WalletModal = () => {
    return (
        <MetaMaskProvider>
            <WalletModalMetamask />
        </MetaMaskProvider>
    )
}

const WalletModalMetamask = () => {
    const { Modal, setOpen } = useMetamaskModal()

    return (
        <>
            <div className="elementor-button-link elementor-button elementor-size-sm login-button" role="button">
                <span className="elementor-button-content-wrapper" onClick={() => setOpen(true)}>
                    <span className="elementor-button-text">Connect to your wallet</span>
                </span>
            </div>
            <Modal />
        </>
    )
}
