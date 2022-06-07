import axios from 'axios'
import './index.scss'

const { useState, useEffect } = wp.element

export const VendorSearch = () => {
    const [vendors, setVendors] = useState([])
    const [showedVendors, setShowedVendors] = useState([])
    const [lastVisitedVendors, setLastVisitedVendors] = useState([])
    useEffect(async () => {
        const response = await axios.get(
            `${window.location.origin}/wp-json/dokan/v1/stores`,
        )
        setVendors(response.data)
        setShowedVendors(response.data)

        const lastVisitedStores = await axios.get(
            `${window.location.origin}/wp-json/api/v1/get-dokan-last-visited-stores`,
            {
                headers: {
                    'X-WP-Nonce': backendData.nonce,
                },
            },
        )
        setLastVisitedVendors(lastVisitedStores.data)
    }, [])

    const onSearch = (event) => {
        const { value } = event.target
        if (value !== '' || !value) {
            setShowedVendors(
                vendors
                    .filter(
                        (vendor) =>
                            vendor.store_name
                                .toLowerCase()
                                .indexOf(value.toLowerCase()) !== -1,
                    )
                    .slice(0, 5),
            )
        } else {
            setShowedVendors([])
        }
    }

    return (
        <div className='header-menu-item'>
            Store List
            <i className='fas fa-chevron-down down-icon-search' />
            <div className='dropdown-content'>
                <a href={`${window.location.origin}/store-listing`}>
                    Explore stores
                </a>
                <div className='search-input-container'>
                    <input
                        className='search-input'
                        type='text'
                        placeholder='Find vendors'
                        onChange={onSearch}
                    />
                    <i className='fa fa-search'></i>
                </div>
                {showedVendors.map((vendor) => (
                    <a href={vendor.shop_url}>
                        <img className='vendor-image' src={vendor.banner} />
                        {vendor.store_name}
                    </a>
                ))}
                {lastVisitedVendors && lastVisitedVendors.length > 0 && (
                    <>
                        <div className='header-subsection'>
                            Previously visited stores
                        </div>
                        {lastVisitedVendors.map((vendor) => (
                            <a href={vendor.shop_url}>
                                <img
                                    className='vendor-image'
                                    src={vendor.banner}
                                />
                                {vendor.store_name}
                            </a>
                        ))}
                    </>
                )}
            </div>
        </div>
    )
}
