const { useState, useEffect } = wp.element
import "./index.scss"

export const MobileMenu = () => {
    const [opened, setOpened] = useState(false)

    useEffect(() => {
        const menuBar = document.getElementById("header-menu-bar-mobile")
        menuBar.addEventListener("click", function () {
            setOpened((prev) => !prev)
        })
    }, [])

    const mobileMenuClass = opened ? "header-menu-items-mobile-opened" : "header-menu-items-mobile"

    return (
        <>
            <div id="header-menu-bar-mobile">
                <i className={`fa fa-${opened ? `times` : `bars`} fa-3x`}></i>
            </div>
            <div className={mobileMenuClass}>
                <MobileLink url="browse" label="Browse" />
                <MobileLink url="create-an-item" label="Create An Item" />
                <MobileDropDown
                    label="Categories"
                    links={[
                        { url: "art", label: "Art" },
                        { url: "watches", label: "Watches" },
                        { url: "wine", label: "Wine" },
                        { url: "supercars", label: "Supercars" },
                        { url: "securities", label: "Securities" },
                        { url: "collectibles", label: "Collectibles" },
                    ]}
                />
                <MobileLink url="store-listing" label="Explore stores" />
            </div>
        </>
    )
}

const MobileDropDown = ({ label, links }) => {
    const [opened, setOpened] = useState(false)

    const onClickHandle = () => {
        setOpened((prevState) => !prevState)
    }

    return (
        <>
            <div className="header-menu-item-mobile" onClick={onClickHandle}>
                <div className="header-menu-item-mobile-dropdown">
                    {label} <i className={`fas fa-chevron-${opened ? "up" : "down"}`}></i>
                </div>
            </div>
            {opened && links.map((link) => <MobileLink url={`product-category/${link.url}`} label={link.label} />)}
        </>
    )
}

const MobileLink = ({ url, label }) => {
    return (
        <div className="header-menu-item-mobile">
            <a href={`${window.location.origin}/${url}`}>{label}</a>
        </div>
    )
}
