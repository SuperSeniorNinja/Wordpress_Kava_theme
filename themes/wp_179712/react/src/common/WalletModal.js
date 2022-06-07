import { useOnClickOutside } from "../hooks/onClickOutside"
import MetamaskImage from "../../assets/svg/metamask.svg"
import { useMetaMask } from "metamask-react"

const { useRef, useState } = wp.element

export const useWalletModal = () => {
    const { connect, status, account } = useMetaMask()
    const [open, setOpen] = useState(false)

    const onCloseClick = () => setOpen((prevState) => !prevState)
    const modalClass = `nanoverse-modal ${open ? "" : "nanoverse-modal-close"}`
    const ref = useRef()

    useOnClickOutside(ref, () => {
        if (open) {
            return onCloseClick()
        }
    })

    const Modal = () => (
        <div className={modalClass}>
            <div ref={ref} className="nanoverse-modal-content">
                <div className="nanoverse-modal-cancel">
                    <i onClick={onCloseClick} className="fas fa-2x fa-times"></i>
                </div>
                <div className="nanoverse-modal-header">Connect to your wallet</div>
                <Metamask metamask={{ connect, status, account }} />
            </div>
        </div>
    )

    return {
        Modal,
        open,
        setOpen,
        connect,
        status,
        account,
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
