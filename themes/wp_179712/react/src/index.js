import { CreateItem } from "./components/createItem/CreateItem"

const { render } = wp.element
import { Explorer } from "./components/explorer/Explorer"
import { VendorSearch } from "./components/vendorSearch/VendorSearch"
import { MobileMenu } from "./components/mobileMenu/MobileMenu"
import { FormBilling } from "./components/formBilling/FormBilling"
import { LoginButton } from "./components/loginButton/LoginButton"

const renderElement = (reactElement, renderTarget) => {
    if (renderTarget) {
        render(reactElement, renderTarget)
    }
}

renderElement(<MobileMenu />, document.getElementById("header-menu-bar-react"))
renderElement(<VendorSearch />, document.getElementById("vendor-search"))
renderElement(<LoginButton />, document.getElementById("nanoverse-wallet-modal"))
renderElement(<FormBilling />, document.getElementById("nanoverse-custom-form"))
renderElement(<Explorer />, document.getElementById("view-my-nfts-container"))
renderElement(<CreateItem />, document.getElementById("create-an-item-container"))
