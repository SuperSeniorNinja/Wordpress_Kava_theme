import { useOnClickOutside } from "../../hooks/onClickOutside"
const { useRef, useState } = wp.element
import "./index.css"

export const useModal = (ModalContent, modalHeader) => {
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
                <div className="nanoverse-modal-header">{modalHeader}</div>
                <ModalContent />
            </div>
        </div>
    )

    return {
        Modal,
        open,
        setOpen,
    }
}
