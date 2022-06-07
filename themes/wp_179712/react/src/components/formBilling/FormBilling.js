import "./index.scss"
import { useWeb3Auth } from "../../hooks/useWeb3Auth"

const NANOVERSE_PREFIX = "nanoverse_"
import MetamaskImage from "../../../assets/svg/metamask.svg"

export const FormBilling = () => {
    return (
        <>
            <div className="nanoverse-checkout-form-header">You Are Acquiring A Business Membership</div>
            <div>
                <div>This pack contains:</div>
                <div className="nanoverse-checkout-pack-contains-items">
                    {[
                        "A payable personalized avatar (its face will looks like youe),",
                        "A 365 Days Membership (Residency included),",
                        "Ability to organize and attend events (organize costs not included),",
                        "A Copy Of Odyssea Republic constitution and commerce rules,",
                        "Pay and get pay in crypto and fiat",
                        "Ability to acquire or rent entire building",
                        "Ability to have employees",
                    ].map((text) => (
                        <PackContainsItem text={text} />
                    ))}
                </div>
            </div>
            <div>
                <div className="nanoverse-checkout-input-row nanoverse-checkout-business-inputs">
                    <input
                        required
                        className="nanoverse-checkout-business-input"
                        name={`${NANOVERSE_PREFIX}business_name`}
                        type="text"
                        placeholder="Business Name"
                    />
                    <input
                        required
                        className="nanoverse-checkout-business-input"
                        name={`${NANOVERSE_PREFIX}industry`}
                        type="text"
                        placeholder="Industry"
                    />
                </div>
                <div className="nanoverse-checkout-upload-business nanoverse-checkout-input-row">
                    <div className="nanoverse-checkout-upload-business-item">Upload:</div>
                    <div className="nanoverse-checkout-upload-business-item">
                        <InputFile id="upload-photo-summary" name="executive_summary" placeholder="Executive Summary" />
                    </div>
                    <div className="nanoverse-checkout-upload-business-item">
                        <InputFile id="upload-photo-pitch" name="executive_pitch_deck" placeholder="Pitch Deck" />
                    </div>
                </div>

                <InputText name="business_owner_name" placeholder="Business Owner Last name, First name" />
                <input
                    required
                    className="nanoverse-checkout-input-row"
                    name={`${NANOVERSE_PREFIX}business_date_of_birth`}
                    type="date"
                    placeholder="Date of Birth"
                />
                <InputText name="main_address" placeholder="Address" />
                <div className="nanoverse-checkout-input-row nanoverse-checkout-address-items">
                    <input
                        required
                        name={`${NANOVERSE_PREFIX}zip_code`}
                        className="nanoverse-checkout-address-item"
                        type="text"
                        placeholder="Zip Code"
                    />
                    <input
                        required
                        name={`${NANOVERSE_PREFIX}city`}
                        className="nanoverse-checkout-address-item"
                        type="text"
                        placeholder="City"
                    />
                    <input
                        required
                        name={`${NANOVERSE_PREFIX}address`}
                        className="nanoverse-checkout-address-item"
                        type="text"
                        placeholder="Country"
                    />
                </div>
                <div className="nanoverse-checkout-input-row nanoverse-checkout-center">
                    <InputFile id="upload-pictures" name="picture_uploads[]" placeholder="Upload 5 pictures" />
                </div>
                <div className="nanoverse-checkout-input-row nanoverse-checkout-center">
                    <InputFile id="upload-passport" name="passport" placeholder="Upload your passport" />
                </div>
                <InputText name="email_address" placeholder="Email Address" />
                <InputText name="phone_number" placeholder="Phone Number" />
                <AddressInput />
            </div>
        </>
    )
}

const AddressInput = () => {
    const { account, login } = useWeb3Auth()
    return (
        <div className="nanoverse-address">
            <img src={MetamaskImage} onClick={login} className="nanoverse-address-metamask-icon" />
            <input
                required
                className="nanoverse-address-input"
                name={`${NANOVERSE_PREFIX}wallet_address`}
                type="text"
                placeholder="Wallet Address"
                value={account}
            />
        </div>
    )
}

const PackContainsItem = ({ text }) => (
    <div className="nanoverse-checkout-pack-contains-item">
        <div className="nanoverse-metrics-key-dot"></div>
        <div>{text}</div>
    </div>
)

const InputText = ({ name, placeholder, value }) => (
    <input
        required
        className="nanoverse-checkout-input-row"
        name={`${NANOVERSE_PREFIX}${name}`}
        type="text"
        placeholder={placeholder}
        value={value}
    />
)

const InputFile = ({ name, id, placeholder }) => (
    <>
        <label htmlFor={id} className="nanoverse-checkout-upload-button">
            {placeholder}
        </label>
        <input
            type="file"
            required
            name={`${NANOVERSE_PREFIX}${name}`}
            id={id}
            multiple="true"
            className="nanoverse-checkout-input-hidden"
        />
    </>
)
