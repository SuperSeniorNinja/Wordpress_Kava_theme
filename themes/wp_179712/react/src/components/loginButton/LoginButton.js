import { useWeb3Auth } from "../../hooks/useWeb3Auth"
import "./index.scss"
import { useModal } from "../../common/modal/Modal"

export const LoginButton = () => {
    const { login, logout, account, user } = useWeb3Auth()
    const ModalContent = () => (
        <div className="modal-logged-content">
            {account && <div className="modal-logged-content-account">{account}</div>}
            <div
                className="elementor-button-link elementor-button elementor-size-sm login-button modal-logged-content-logout"
                role="button"
            >
                {account ? (
                    <span className="elementor-button-content-wrapper" onClick={logout}>
                        <span className="elementor-button-text">Log out</span>
                    </span>
                ) : (
                    <span className="elementor-button-content-wrapper" onClick={() => setOpen(false)}>
                        <span className="elementor-button-text">Close</span>
                    </span>
                )}
            </div>
        </div>
    )

    const { Modal, setOpen } = useModal(ModalContent, account ? "You are logged in." : "You are logged out.")

    return (
        <>
            <div className="elementor-button-link elementor-button elementor-size-sm login-button" role="button">
                <span className="elementor-button-content-wrapper" onClick={account ? () => setOpen(true) : login}>
                    <span className="elementor-button-text">{account ? "Wallet connected" : "Connect wallet"}</span>
                </span>
            </div>
            <Modal />
        </>
    )
}
